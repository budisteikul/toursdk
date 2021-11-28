<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Models\Category;
use budisteikul\toursdk\Models\Review;
use budisteikul\toursdk\Helpers\ProductHelper;
use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\ImageHelper;
use budisteikul\toursdk\Helpers\GeneralHelper;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Channel;
use budisteikul\toursdk\Models\Page;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartPayment;
use budisteikul\toursdk\Models\ShoppingcartProduct;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use budisteikul\toursdk\Helpers\PaypalHelper;
use budisteikul\toursdk\Helpers\MidtransHelper;
use Barryvdh\DomPDF\Facade as PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Cache;

class APIController extends Controller
{

    public function __construct()
    {
        $this->bookingChannelUUID = env("BOKUN_BOOKING_CHANNEL");
        $this->currency = env("BOKUN_CURRENCY");
        $this->lang = env("BOKUN_LANG");

        $this->paypalClientId = env("PAYPAL_CLIENT_ID");
        $this->paypalCurrency = env("PAYPAL_CURRENCY");
        
        $this->midtransServerKey = env("MIDTRANS_SERVER_KEY");
        $this->midtransClientKey = env("MIDTRANS_CLIENT_KEY");
        $this->midtransEnv = env("MIDTRANS_ENV");
        $this->appName = env("APP_NAME");
        $this->appUrl = env("APP_URL");
    }

    public function test()
    {
        //BookingHelper::get_firstAvailability(7424,2021,11);
    }

    public function last_order($sessionId)
    {
        $shoppingcarts = Shoppingcart::where('session_id', $sessionId)->orderBy('id','desc')->get();
        
        $booking = array();
        foreach($shoppingcarts as $shoppingcart)
        {
            $invoice = BookingHelper::display_invoice($shoppingcart);

            $product = BookingHelper::display_product_detail($shoppingcart);
            
            $receipt_page = '<a class="text-decoration-none text-theme" href="'.$this->appUrl.'/booking/receipt/'.$shoppingcart->id.'/'. $shoppingcart->session_id .'">View receipt page</a>';

            $booking[] = array(
                'booking' => $invoice .'<hr style="width:50%; margin-left:0px;" />'. $product . $receipt_page
            );
        }

        return response()->json([
                'message' => 'success',
                'booking' => $booking
            ], 200);
        
    }

    public function product_add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        Cache::forget('_bokunProductById_'. $this->currency .'_'. $this->lang .'_'.$data);
        BokunHelper::get_product($data);
        return response()->json([
                'message' => 'success'
            ], 200);
    }

    public function product_remove(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        Cache::forget('_bokunProductById_'. $this->currency .'_'. $this->lang .'_'.$data);
        return response()->json([
                'message' => 'success'
            ], 200);
    }

    public function ticket_check(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $confirmation_code = strtoupper(trim($data['confirmation_code']));
        $shoppingcart = Shoppingcart::where('confirmation_code',$confirmation_code)->first();
        

        if($shoppingcart)
        {
            return response()->json([
                'message' => 'success',
                'sessionId' => $shoppingcart->session_id,
                'redirect' => '/booking/receipt/'.$shoppingcart->id.'/'.$shoppingcart->session_id
            ], 200);
        }
        else
        {
                return response()->json([
                    'message' => 'fail'
                ], 200);
        }
    }
    
    public function categories()
    {

        $dataObj = array();
        $categories = Category::get();
        foreach($categories as $category)
        {
            $dataObj2 = array();
            foreach($category->product()->orderBy('id','asc')->get() as $product)
            {
                
                $content = BokunHelper::get_product($product->bokun_id);
                $cover = ImageHelper::cover($product);
                $dataObj2[] = array(
                    'id' => $product->id,
                    'cover' => $cover,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'excerpt' => $content->excerpt,
                    'duration' => $content->durationText,
                    'currency' => $content->nextDefaultPriceMoney->currency,
                    'amount' => GeneralHelper::numberFormat($content->nextDefaultPriceMoney->amount),
                );
                
            }

            $dataObj[] = array(
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'products' => $dataObj2,
            );


        }

        return response()->json([
            'message' => 'success',
            'categories' => $dataObj
        ], 200);
        
    }
    
    public function navbar()
    {
        $categories = Category::where('parent_id',0)->get();
        return response()->json([
            'message' => 'success',
            'categories' => $categories
        ], 200);
    }

    

    public function tawkto($id)
    {
        $jscript = '
        function tawktoScript()
        {
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        (function(){
        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
        s1.async=true;
        s1.src=\'https://embed.tawk.to/'. $id .'/default\';
        s1.charset=\'UTF-8\';
        s1.setAttribute(\'crossorigin\',\'*\');
        s0.parentNode.insertBefore(s1,s0);
        })();
        }
        ';
        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function category($slug)
    {
        $category = Category::where('slug',$slug)->firstOrFail();
        $products = ProductHelper::getProductByCategory($category->id);
        
        $dataObj = array();
        $dataObj2 = array();
        foreach($products as $product)
        {
            
            $content = BokunHelper::get_product($product->bokun_id);
            
            $cover = ImageHelper::cover($product);
            $dataObj2[] = array(
                'id' => $product->id,
                'cover' => $cover,
                'name' => $product->name,
                'slug' => $product->slug,
                'excerpt' => $content->excerpt,
                'duration' => $content->durationText,
                'currency' => $content->nextDefaultPriceMoney->currency,
                'amount' => GeneralHelper::numberFormat($content->nextDefaultPriceMoney->amount),
            );
            
        }
        
        $dataObj[] = array(
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'products' => $dataObj2,
            );

        
        return response()->json([
            'message' => 'success',
            'category' => $dataObj,
        ], 200);
    }

    public function page($slug)
    {
        $page = Page::where('slug',$slug)->firstOrFail();
        $dataObj[] = array(
            'title' => $page->title,
            'content' => $page->content,
        );
        return response()->json([
                'page' => $dataObj
            ], 200);
    }

    public function product($slug)
    {

        $product = Product::where('slug',$slug)->firstOrFail();

        $dataObj = array();
        $dataObj2 = array();
        $dataObj3 = array();
        $dataObj4 = array();
        $dataObj5 = array();
        
        $content = BokunHelper::get_product($product->bokun_id);

        $i = 0;
        $carouselExampleIndicators = '';
        $carouselInners = '';
        foreach($product->images->sortBy('sort') as $image)
        {
            $active = '';
            if($i==0) $active = 'active';

            $carouselInners .= '<div class="carousel-item '.$active.'"><img class="d-block w-100" src="'.ImageHelper::urlImageCloudinary($image->public_id,600,400).'" alt="'.$product->name.'"  /></div>';

            $carouselExampleIndicators .= '<li data-target="#carouselExampleIndicators" data-slide-to="'.$i.'"></li>';

            $i++;
        }

        $image = '
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                '.$carouselExampleIndicators.'
            </ol>

            <div class="carousel-inner">
                '.$carouselInners.'
            </div>
          
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>';

        $pickup = '';
        if($content->meetingType=='PICK_UP' || $content->meetingType=='MEET_ON_LOCATION_OR_PICK_UP')
        {
            $pickup = BokunHelper::get_product_pickup($content->id);
        }


        if(!empty($pickup))
        {
            for($i=0;$i<count($pickup);$i++)
            {
                $dataObj3[] = array(
                            'title' => $pickup[$i]->title,
                        );
            }
        }


        if(!empty($content->startPoints))
        {
            $dataObj2[] = array(
                            'title' => $content->startPoints[0]->title,
                            'addressLine1' => $content->startPoints[0]->address->addressLine1,
                            'addressLine2' => $content->startPoints[0]->address->addressLine2,
                            'addressLine3' => $content->startPoints[0]->address->addressLine3,
                            'city' => $content->startPoints[0]->address->city,
                            'state' => $content->startPoints[0]->address->state,
                            'postalCode' => $content->startPoints[0]->address->postalCode,
                            'countryCode' => $content->startPoints[0]->address->countryCode,
                            'latitude' => $content->startPoints[0]->address->geoPoint->latitude,
                            'longitude' => $content->startPoints[0]->address->geoPoint->longitude
                        );
        }

        $difficultyLevel = '';
        if($content->difficultyLevel!="") $difficultyLevel = ProductHelper::lang('dificulty',$content->difficultyLevel);

        $productCategory = ProductHelper::lang('type',$content->productCategory);

        if(!empty($content->guidanceTypes))
        {
            if($content->guidanceTypes[0]->guidanceType=="GUIDED")
            {
                for($i=0;$i<count($content->guidanceTypes[0]->languages);$i++)
                {
                    $dataObj4[] = array(
                            'language' => ProductHelper::lang('language',$content->guidanceTypes[0]->languages[$i]),
                        );
                    
                }
            }
        }


        if(!empty($content->agendaItems))
        {
            foreach($content->agendaItems as $agendaItem)
            {
                $dataObj5[] = array(
                    'title' => $agendaItem->title,
                    'body' => $agendaItem->body,
                );
            }
        }

        $excerpt = null;
        $included = null;
        $excluded = null;
        $requirements = null;
        $attention = null;
        $durationText = null;
        $privateActivity = null;
        $description = null;

        if($content->excerpt!="") $excerpt = $content->excerpt;
        if($content->included!="") $included = $content->included;
        if($content->excluded!="") $excluded = $content->excluded;
        if($content->requirements!="") $requirements = $content->requirements;
        if($content->attention!="") $attention = $content->attention;
        if($content->durationText!="") $durationText = $content->durationText;
        if($content->privateActivity!="") $privateActivity = $content->privateActivity;
        if($content->description!="") $description = $content->description;

        $dataObj[] = array(
                'id' => $product->id,
                'name' => $product->name,
                'durationText' => $durationText,
                'difficultyLevel' => $difficultyLevel,
                'privateActivity' => $privateActivity,
                'excerpt' => $excerpt,
                'startPoints' => $dataObj2,
                'description' => $description,
                'included' => $included,
                'excluded' => $excluded,
                'requirements' => $requirements,
                'attention' => $attention,
                'pickupPlaces' => $dataObj3,
                'productCategory' => $productCategory,
                'guidanceTypes' => $dataObj4,
                'agendaItems' => $dataObj5,
                'images' => $image,
            );

        
        return response()->json([
            'message' => 'success',
            'product' => $dataObj,
        ], 200);

    }

    public function product_jscript($slug,$sessionId,Request $request)
    {
        $data = $request->input('embedded');
        $product = Product::where('slug',$slug)->first();
        if($product)
        {
            $content = BokunHelper::get_product($product->bokun_id);
            $calendar = BokunHelper::get_calendar_new($content->id);

            $availability = BookingHelper::get_firstAvailability($content->id,$calendar->year,$calendar->month);
            
            $microtime = $availability[0]['date'];
            $month = date("n",$microtime/1000);
            $year = date("Y",$microtime/1000);

            $embedded = "true";
            $inject_script = '';
            if($data=="false")
            {
                $embedded = "false";
                $inject_script = '$("#titleProduct").append(\''. $product->name .'\');';
            }
        

            $jscript = ' 
            
            window.priceFormatter = new WidgetUtils.PriceFormatter({
                currency: \''. $this->currency .'\',
                language: \''. $this->lang .'\',
                decimalSeparator: \'.\',
                groupingSeparator: \',\',
                symbol: \''. $this->currency .' \'
            });

            window.i18nLang = \''. $this->lang .'\';

            '. $inject_script .'
        
            window.ActivityBookingWidgetConfig = {
                currency: \''. $this->currency .'\',
                language: \''. $this->lang .'\',
                embedded: '.$embedded.',
                priceFormatter: window.priceFormatter,
                invoicePreviewUrl: \''.url('/api').'/activity/invoice-preview\',
                addToCartUrl: \''.url('/api').'/widget/cart/session/'.$sessionId.'/activity\',
                calendarUrl: \''.url('/api').'/activity/{id}/calendar/json/{year}/{month}\',
                activities: [],
                pickupPlaces: [],
                dropoffPlaces: [],
                showOnRequestMessage: false,
                showCalendar: true,
                showUpcoming: false,
                displayOrder: \'Calendar\',
                selectedTab: \'all\',
                hideExtras: false,
                showActivityList: false,
                showFewLeftWarning: false,
                warningThreshold: 10,
                displayStartTimeSelectBox: false,
                displayMessageAfterAddingToCart: false,
                defaultCategoryMandatory: true,
                defaultCategorySelected: true,
                affiliateCodeFromQueryString: true,
                affiliateParamName: \'trackingCode\',
                affiliateCode: \'\',
                onAfterRender: function() {

                    $(".PICK_UP").hide();

                    $("#proses").remove();

                    if ( window.widgetIframe != undefined ) { window.widgetIframe.autoResize(); }
                    setTimeout(function() {
                        if ( window.widgetIframe != undefined ) { window.widgetIframe.autoResize(); }
                    }, 200);

                    if (typeof onWidgetRender !== \'undefined\') {
                        onWidgetRender();
                    }
                },
                onAvailabilitySelected: function(selectedRate, selectedDate, selectedAvailability) {
                },
                onAddedToCart: function(cart) {
                    $(\'.btn-primary\').attr("disabled",true);
                    $(\'.btn-primary\').html(\' <i class="fa fa-spinner fa-spin fa-fw"></i>  processing... \');
                    window.openAppRoute(\'/booking/checkout\');    
                },
        
                calendarMonth: '.$month.',
                calendarYear: '.$year.',
                loadingCalendar: true,
        
                activity: '.json_encode($content).',
        
                upcomingAvailabilities: [],
        
                firstDayAvailabilities: '.json_encode($availability).'
            };';   
        }
        else
        {
            $jscript = 'window.openAppRoute(\'/page/not/found\')'; 
        }

        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function review_jscript()
    {
        $jscript = '
        jQuery(document).ready(function($) {  
            var table = $("#dataTables-example").DataTable(
            {
                "processing": true,
                "serverSide": true,
                "ajax": 
                {
                    "url": "'.url('/api').'/review",
                    "type": "POST",
                },
                "scrollX": true,
                "language": 
                {
                    "paginate": 
                    {
                        "previous": "<i class=\"fa fa-step-backward\"></i>",
                        "next": "<i class=\"fa fa-step-forward\"></i>",
                        "first": "<i class=\"fa fa-fast-backward\"></i>",
                        "last": "<i class=\"fa fa-fast-forward\"></i>"
                    },
                    "aria": 
                    {
                        "paginate": 
                        {
                            "first":    "First",
                            "previous": "Previous",
                            "next":     "Next",
                            "last":     "Last"
                        }
                    }
                },
                "pageLength": 5,
                "order": [[ 0, "desc" ]],
                "columns": [
                    {data: "date", name: "date", orderable: true, searchable: false, visible: false},
                    {data: "style", name: "style", className: "auto", orderable: false},
                ],
                "dom": "tp",
                "pagingType": "full_numbers",
                
                "fnDrawCallback": function () {
                    
                    try {
                        document.getElementById("loadingReviews").style.display = "none";
                        document.getElementById("dataTables-example").style.display = "block";
                    }
                    catch(err) {
  
                    }
                    
                }
            });
            
            

            table.on("page.dt", function(o){
                var oldStart = 0;
                if ( o._iDisplayStart != oldStart ) {
                    var targetOffset = $("#review").offset().top;
                    $("html, body").animate({scrollTop: targetOffset}, 500, "easeInOutExpo");
                    document.getElementById("loadingReviews").style.display = "block";
                    document.getElementById("dataTables-example").style.display = "none";
                    oldStart = o._iDisplayStart;
                    $("#reviewLink").focus();
                }

                
            });
            
      
      });';
      return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function review_count()
    {
        $count = Review::count();
        return response()->json([
            'message' => 'success',
            'count' => $count
        ], 200);
    }

    public function review(Request $request)
    {
            $resources = Review::query();
            return Datatables::eloquent($resources)
                ->addColumn('style', function ($resource) {
                    $rating = $resource->rating;
                    switch($rating)
                    {
                        case '1':
                            $star ='<i class="fa fa-star"></i>';    
                        break;
                        case '2':
                            $star ='<i class="fa fa-star"></i><i class="fa fa-star"></i>';  
                        break;
                        case '3':
                            $star ='<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';    
                        break;
                        case '4':
                            $star ='<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';  
                        break;
                        case '5':
                            $star ='<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';    
                        break;
                        default:
                            $star ='<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';    
                    }
                    
                    
                    $title = '<b>'.$resource->title.'</b><br>';
                    $date = Carbon::parse($resource->date)->formatLocalized('%b, %Y');
                    $user = '<b>'. $resource->user .'</b> <small><span class="text-muted">'.$date.'</span></small><br>';
                    $rating = '<span class="text-warning">'. $star .'</span>‎<br>';
                    $text =  nl2br($resource->text) .'<br>';
                    $post_title = 'Review of : <b>'. Product::findOrFail($resource->product_id)->name.'</b><br>';
                    $channel_name = Channel::find($resource->channel_id)->name;

                    if($resource->link!="")
                    {
                        $from = '<a href="'. $resource->link .'"  rel="noreferrer noopener" target="_blank" class="text-theme"><b>'.$channel_name.'</b></a>';
                    }
                    else
                    {
                        $from = '<b>'.$channel_name.'</b>';
                    }

                    $output = $user.$post_title.$rating.$title.$text.$from;
                    
                    return '<div class="bd-callout bd-callout-theme shadow-sm rounded" style="margin-top:5px;margin-bottom:5px;" >'. $output .'</div>';
                })
                ->rawColumns(['style'])
                ->toJson();
    }

    public function addshoppingcart($id,Request $request)
    {
        $contents = BokunHelper::get_addshoppingcart($id,json_decode($request->getContent(), true));
        
        $sessionId = $id;
        
        $value = Cache::get('_'. $id, 'empty');
        if($value=='empty')
        {
            BookingHelper::get_shoppingcart($sessionId,"insert",$contents);
        }
        else
        {

            BookingHelper::get_shoppingcart($sessionId,"update",$contents);
        }

        return response()->json($contents);
    }

    public function shoppingcart(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $sessionId = $data['sessionId'];


        $shoppingcart = Cache::get('_'. $sessionId);

        
        $dataObj = array();
        $dataObj2 = array();
        

        foreach(collect($shoppingcart->products)->sortBy('booking_id') as $shoppingcart_product)
        {
            
            $product_subtotal = 0;
            $product_discount = 0;
            $product_total = 0;
            $product_detail_asText = '';
            

            foreach($shoppingcart_product->product_details as $product_detail)
            {
                
                if($product_detail->type=="product")
                {
                    $product_subtotal += $product_detail->subtotal;
                    $product_discount += $product_detail->discount;
                    $product_total += $product_detail->total;
                    $product_detail_asText .= $product_detail->qty .' x '. $product_detail->unit_price .' ('. GeneralHelper::numberFormat($product_detail->price) .') <br />';
                }

            }

            

            if($product_discount>0)
            {
                $product_total_asText = '<strike className="text-muted">'.GeneralHelper::numberFormat($product_subtotal).'</strike><br /><b>'.GeneralHelper::numberFormat($product_total).'</b>';
            }
            else
            {
                $product_total_asText = '<b>'.GeneralHelper::numberFormat($product_total).'</b>';
            }

            $dataObj3 = array();
            foreach($shoppingcart_product->product_details as $product_detail)
            {
                if($product_detail->type=="pickup")
                {
                    if($product_detail->discount > 0)
                    {
                        $pickup_price_asText = '<strike className="text-muted">'. GeneralHelper::numberFormat($product_detail->subtotal) .'</strike><br /><b>'. GeneralHelper::numberFormat($product_detail->total) .'</b>';
                    }
                    else
                    {
                        $pickup_price_asText = '<b>'. GeneralHelper::numberFormat($product_detail->total) .'</b>';
                    }

                    $dataObj3[] = array(
                        'title' => 'Pick-up and drop-off services',
                        'price' => $pickup_price_asText,
                        'unit_price' => $product_detail->unit_price,
                    );

                }
            }

            
            $dataObj4 = array();
            foreach($shoppingcart_product->product_details as $product_detail)
            {
                if($product_detail->type=="extra")
                {
                    $extra_unit_price_asText = '&#9642; '. $product_detail->qty .' '. $product_detail->unit_price;
                    if($product_detail->discount > 0)
                    {
                        $extra_price_asText = '<strike className="text-muted">'. GeneralHelper::numberFormat($product_detail->subtotal) .'</strike><br /><b>'. GeneralHelper::numberFormat($product_detail->total) .'</b>';
                    }
                    else
                    {
                        $extra_price_asText = '<b>'. GeneralHelper::numberFormat($product_detail->total) .'</b>';
                    }

                    $dataObj4[] = array(
                        'title' => 'Extra',
                        'price' => $extra_price_asText,
                        'unit_price' => $extra_unit_price_asText,
                    );

                }
            }

            $dataObj2[] = array(
                'booking_id' => $shoppingcart_product->booking_id,
                'title' => $shoppingcart_product->title,
                'product_total' => $product_total_asText,
                'image' => $shoppingcart_product->image,
                'date' => ProductHelper::datetotext($shoppingcart_product->date),
                'rate' => $shoppingcart_product->rate,
                'product_detail' => $product_detail_asText,
                'pickups' => $dataObj3,
                'extras' => $dataObj4,
            );
            
        }    
        
           
        $dataObj5 = array();
        foreach(collect($shoppingcart->questions)->sortBy('order') as $shoppingcart_question)
        {
            if($shoppingcart_question->type=='mainContactDetails')
            {
                $dataObj5[] = array(
                    'question_id' => $shoppingcart_question->question_id,
                    'required' => $shoppingcart_question->required,
                    'data_format' => $shoppingcart_question->data_format,
                    'label' => $shoppingcart_question->label,
                    'answer' => $shoppingcart_question->answer,
                );
            }
        }

        
        $dataObj6 = array();
        foreach(collect($shoppingcart->products)->sortBy('booking_id') as $shoppingcart_product)
        {
            
            $dataObj7 = array();
            foreach(collect($shoppingcart->questions)->sortBy('order') as $shoppingcart_question)
            {

                if($shoppingcart_product->booking_id===$shoppingcart_question->booking_id)
                {
                    //
                    if($shoppingcart_question->booking_id!="")
                    {
                        
                        $dataObj7[] = array(
                            'question_id' => $shoppingcart_question->question_id,
                            'required' => $shoppingcart_question->required,
                            'label' => $shoppingcart_question->label,
                            'help' => $shoppingcart_question->help,
                            'answer' => $shoppingcart_question->answer,
                            'booking_id' => $shoppingcart_question->booking_id,
                        );
                    }
                }

            }

            $dataObj6[] = array(
                        'title' => $shoppingcart_product->title,
                        'description' => ProductHelper::datetotext($shoppingcart_product->date),
                        'questions' => $dataObj7
                    );

        }

        
        
        $promo_code = $shoppingcart->promo_code;
        if($promo_code=="") $promo_code = null;
        


        $dataObj[] = array(
                'id' => $shoppingcart->session_id,
                'confirmation_code' => $shoppingcart->confirmation_code,
                'promo_code' => $shoppingcart->promo_code,
                'currency' => $shoppingcart->currency,
                'subtotal' => GeneralHelper::numberFormat($shoppingcart->subtotal),
                'discount' => GeneralHelper::numberFormat($shoppingcart->discount),
                'total' => GeneralHelper::numberFormat($shoppingcart->total),
                'total_paypal' => BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,env("PAYPAL_CURRENCY")),
                'due_now' => GeneralHelper::numberFormat($shoppingcart->due_now),
                'due_on_arrival' => GeneralHelper::numberFormat($shoppingcart->due_on_arrival),
                'products' => $dataObj2,
                'mainQuestions' => $dataObj5,
                'productQuestions' => $dataObj6,
                'rate' => BookingHelper::paypal_rate($shoppingcart),
                'paypal_client_id' => $this->paypalClientId,
                'paypal_currency' => $this->paypalCurrency,
                'midtrans_env' => $this->midtransEnv,
                'midtrans_client_key' => $this->midtransClientKey,
            );

        return response()->json([
            'message' => 'success',
            'shoppingcarts' => $dataObj,
        ], 200);
    }
    

    public function removebookingid(Request $request)
    {
        $data = json_decode($request->getContent(), true);


        $validator = Validator::make($data, [
            'bookingId' => ['required', 'integer'],
            'sessionId' => ['required', 'string', 'max:255'],
        ]);


        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $sessionId = $data['sessionId'];
        $bookingId = $data['bookingId'];
         
        BookingHelper::remove_activity($sessionId,$bookingId);
        
            return response()->json([
                    "message" => "success"
                ]);
    }

    public function removepromocode(Request $request)
    {
        $validator = Validator::make(json_decode($request->getContent(), true), [
            'sessionId' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        
        $data = json_decode($request->getContent(), true);

        $sessionId = $data['sessionId'];

        BookingHelper::remove_promocode($sessionId);
        
        return response()->json([
                'message' => 'success'
            ], 200);
    }

    public function applypromocode(Request $request)
    {
        $validator = Validator::make(json_decode($request->getContent(), true), [
            'promocode' => ['required', 'string', 'max:255'],
            'sessionId' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $data = json_decode($request->getContent(), true);
        
        $promocode = $data['promocode'];
        $sessionId = $data['sessionId'];

        $status = BookingHelper::apply_promocode($sessionId,$promocode);

        if($status)
        {
            return response()->json([
                'message' => 'success'
            ], 200);
        }
        else
        {
            return response()->json([
                'message' => 'failed'
            ], 200);
        }
    }

    public function snippetsinvoice(Request $request)
    {
        
        $contents = BokunHelper::get_invoice(json_decode($request->getContent(), true));
        return response()->json($contents);
    }

    public function snippetscalendar($activityId,$year,$month)
    {
        $contents = BookingHelper::get_calendar($activityId,$year,$month);
        return response()->json($contents);
    }

    public function checkout(Request $request)
    {
            $validator = Validator::make(json_decode($request->getContent(), true), [
                'sessionId' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json($errors);
            }
            
            $data = json_decode($request->getContent(), true);

            $sessionId = $data['sessionId'];
            
            $check_question = BookingHelper::check_question_json($sessionId,$data);
            if(@count($check_question) > 0)
            {
                return response()->json($check_question);
            }
            $shoppingcart = BookingHelper::save_question_json($sessionId,$data);
            
            


            return response()->json([
                'message' => 'success',
            ], 200);
    }

    public function receipt($id,$sessionId)
    {
        $shoppingcart = Shoppingcart::where('id',$id)->where('session_id', $sessionId)->where(function($query){
            return $query->where('booking_status', 'CONFIRMED')
                         ->orWhere('booking_status', 'CANCELED')
                         ->orWhere('booking_status', 'PENDING');
        })->firstOrFail();
        
        $invoice = 'No Documents';
        try {
            if($shoppingcart->shoppingcart_payment->payment_status>0) {
                $invoice = '<a target="_blank" class="text-theme" href="'.url('/api').'/pdf/invoice/'. $shoppingcart->session_id .'/Invoice-'. $shoppingcart->confirmation_code .'.pdf"><i class="fas fa-file-invoice"></i> Invoice-'. $shoppingcart->confirmation_code .'.pdf</a><br />';
            }
        } catch (Exception $e) {
        }

        $ticket = '';
        try {
            if($shoppingcart->shoppingcart_payment->payment_status==2 || $shoppingcart->shoppingcart_payment->payment_status==1) {
                foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product) {
                    $ticket .= '<a target="_blank" class="text-theme" href="'.url('/api').'/pdf/ticket/'.$shoppingcart->session_id.'/Ticket-'.$shoppingcart_product->product_confirmation_code.'.pdf"><i class="fas fa-ticket-alt"></i> Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf</a>
                                <br />';
                }
            }
        } catch (Exception $e) {
        }
        
        if($ticket=="") $ticket = 'No Documents <br /><small class="form-text text-muted">* Available when status is paid</small>';

        
        $pdfUrl = array();
        
        if($shoppingcart->shoppingcart_payment->payment_provider=="midtrans") {
            $pdfUrl = '<a target="_blank" class="text-theme" href="'.url('/api').'/pdf/instruction/'. $shoppingcart->session_id .'/Instruction-'. $shoppingcart->confirmation_code .'.pdf"><i class="fas fa-file-invoice"></i> Instruction-'. $shoppingcart->confirmation_code .'.pdf</a><br />';
        }

        $payment_status_asText = BookingHelper::get_paymentStatus($shoppingcart);
        $booking_status_asText = BookingHelper::get_bookingStatus($shoppingcart);
        //$status_asText = BookingHelper::payment_status_public($shoppingcart->shoppingcart_payment->payment_status);
        //if($shoppingcart->booking_status=="CANCELED") $status_asText = '<span class="badge badge-danger">CANCELED</span>';

        $main_contact = BookingHelper::get_answer_contact($shoppingcart);
        
        $dataObj = array(
            'vendor' => $this->appName,
            'booking_status' => $shoppingcart->booking_status,
            'booking_status_asText' => $booking_status_asText,
            'confirmation_code' => $shoppingcart->confirmation_code,
            'total' => $shoppingcart->currency .' '. GeneralHelper::numberFormat($shoppingcart->due_now),
            'payment_status' => $shoppingcart->shoppingcart_payment->payment_status,
            'payment_status_asText' => $payment_status_asText,
            'firstName' => $main_contact->firstName,
            'lastName' => $main_contact->lastName,
            'phoneNumber' => $main_contact->phoneNumber,
            'email' => $main_contact->email,
            'invoice' => $invoice,
            'tickets' => $ticket,
            'paymentProvider' => $shoppingcart->shoppingcart_payment->payment_provider,
            'pdf_url' => $pdfUrl,
        );
        
        return response()->json([
                'receipt' => $dataObj
            ], 200);
    }
    
    public function instruction($sessionId,$id)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$id)->where('session_id',$sessionId)->firstOrFail();
        
        $customPaper = array(0,0,430,2032);
        $pdf = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.manual.bank_transfer', compact('shoppingcart'))->setPaper($customPaper,'portrait');
        return $pdf->download('Instruction-'. $shoppingcart->confirmation_code .'.pdf');
    }

    public function invoice($sessionId,$id)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$id)->where('session_id',$sessionId)->firstOrFail();

        $notice = '';
        $qrcode = base64_encode(QrCode::errorCorrection('H')->format('png')->size(111)->margin(0)->generate( $this->appUrl .'/booking/receipt/'.$shoppingcart->id.'/'.$shoppingcart->session_id  ));

        if(isset($shoppingcart->shoppingcart_payment->currency))
        {
            if($shoppingcart->currency!=$shoppingcart->shoppingcart_payment->currency)
            {
                $notice .= 'Pay : '.$shoppingcart->shoppingcart_payment->currency.' '. $shoppingcart->shoppingcart_payment->amount .'<br />';
                $notice .= 'Paypal Rate : '. BookingHelper::get_rate($shoppingcart) .'<br />';
            }
        }

        if($shoppingcart->due_on_arrival>0)
        {
            $notice .= 'Due on arrival : '.$shoppingcart->currency.' '. GeneralHelper::numberFormat($shoppingcart->due_on_arrival) .'<br />';
        }
        
        $pdf = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.invoice', compact('shoppingcart','notice','qrcode'))->setPaper('a4', 'portrait');
        return $pdf->download('Invoice-'. $shoppingcart->confirmation_code .'.pdf');
    }
    
    public function ticket($sessionId,$id)
    {
        $shoppingcart_product = ShoppingcartProduct::where('product_confirmation_code',$id)->whereHas('shoppingcart', function($query) use ($sessionId){
            return $query->where('session_id', $sessionId)->where('booking_status','CONFIRMED');
        })->firstOrFail();
        
        $customPaper = array(0,0,300,540);
        $qrcode = base64_encode(QrCode::errorCorrection('H')->format('png')->size(111)->margin(0)->generate( $this->appUrl .'/booking/receipt/'.$shoppingcart_product->shoppingcart->id.'/'.$shoppingcart_product->shoppingcart->session_id  ));

        $pdf = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.ticket', compact('shoppingcart_product','qrcode'))->setPaper($customPaper);
        return $pdf->download('Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf');
    }

    public function confirmpaymentpaypal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderID' => ['required', 'string', 'max:255'],
            'authorizationID' => ['required', 'string', 'max:255'],
            'sessionId' => ['required', 'string', 'max:255'],
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        
        $orderID = $request->input('orderID');
        $authorizationID = $request->input('authorizationID');
        $sessionId = $request->input('sessionId');
        
        $shoppingcart = Cache::get('_'. $sessionId);

        $grand_total = $shoppingcart->payment->amount;
        $payment_total = PaypalHelper::getOrder($orderID);
        
        if($payment_total!=$grand_total)
        {
            PaypalHelper::voidPaypal($authorizationID);
            return response()->json([
                    "id" => "2",
                    "message" => 'Payment Not Valid'
                ]);
        }
        
        $shoppingcart->payment->order_id = $orderID;
        $shoppingcart->payment->authorization_id = $authorizationID;
        $shoppingcart->payment->payment_status = 1;
        
        Cache::forget('_'. $sessionId);
        Cache::add('_'. $sessionId, $shoppingcart, 172800);
        
        BookingHelper::set_bookingStatus($sessionId,'CONFIRMED');

        $shoppingcart = BookingHelper::confirm_booking($sessionId);

        return response()->json([
                    "id" => "1",
                    "message" => "/booking/receipt/".$shoppingcart->id."/".$shoppingcart->session_id
                ]);
        
    }

    public function confirmpaymentmidtrans(Request $request)
    {
        
        switch ($request->method()) {
        case 'POST':
        
            $data = $request->all();
            
            $shoppingcart = Shoppingcart::where('confirmation_code',$data['order_id'])->first();
            if(@count($shoppingcart))
            {
                if(hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].$this->midtransServerKey)==$data['signature_key'])
                {
                    if($data['transaction_status']=="settlement")
                    {
                        $shoppingcart->booking_status = 'CONFIRMED';
                        $shoppingcart->shoppingcart_payment->payment_status = 2;
                        $shoppingcart->shoppingcart_payment->save();
                        BookingHelper::shoppingcart_mail($shoppingcart);
                        
                    }
                    else if($data['transaction_status']=="pending")
                    {
                        $shoppingcart->booking_status = 'PENDING';
                        $shoppingcart->shoppingcart_payment->payment_status = 4;
                        $shoppingcart->shoppingcart_payment->save();
                        
                    }
                    else
                    {
                        $shoppingcart->shoppingcart_payment->payment_status = 0;
                        $shoppingcart->shoppingcart_payment->save();
                        $shoppingcart->booking_status = 'CANCELED';
                        $shoppingcart->save();
                        
                    }
                }
            }

            return response('Always Success', 200)->header('Content-Type', 'text/plain');
            break;

        default:
            $shoppingcart = Shoppingcart::where('confirmation_code',$request->input('order_id'))->firstOrFail();
            return redirect($this->appUrl .'/booking/receipt/'.$shoppingcart->id.'/'.$shoppingcart->session_id);
            break;
        }
        
    }

    public function createpaymentpaypal(Request $request)
    {
        $sessionId = $request->header('sessionId');
        BookingHelper::set_confirmationCode($sessionId);
        $response = BookingHelper::create_payment($sessionId,"paypal");
        return response()->json($response);
    }


    public function createpaymentmidtrans(Request $request)
    {
        $sessionId = $request->input('sessionId');
        
        BookingHelper::set_bookingStatus($sessionId,'PENDING');

        BookingHelper::set_confirmationCode($sessionId);

        BookingHelper::create_payment($sessionId,"midtrans");

        $shoppingcart = BookingHelper::confirm_booking($sessionId);

        return response()->json([
            "id" => "1",
            "token" => $shoppingcart->shoppingcart_payment->snaptoken,
            "redirect" => '/booking/receipt/'.$shoppingcart->id.'/'.$shoppingcart->session_id
        ]);
        
        
    }

    public function midtrans_jscript($sessionId)
    {
           
        $jscript = '
        function midtransScript()
        {
            $("#paymentContainer").html(\'<div id=\"loader\"></div>\');
            $("#submitCheckout").slideUp("slow");
            $("#loader").addClass("loader");

            $.ajax({
                data: {
                    "sessionId": "'.$sessionId.'",
                },
                type: \'POST\',
                url: \''. url('/api') .'/payment/midtrans\'
            }).done(function( data ) {
                if(data.id=="1")
                {
                    window.openAppRoute(data.redirect);
                }
            });

        } 
        ';

        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function paypal_jscript($sessionId)
    {
        
        $jscript = '
        jQuery(document).ready(function($) {

            $("#submitCheckout").slideUp("slow");  
            $("#paymentContainer").html(\'<div id="proses"><h2>Pay with</h2><div id="paypal-button-container"></div></div><div id=\"loader\"></div>\');

            paypal.Buttons({
                createOrder: function() {
                    return fetch(\''. url('/api') .'/payment/paypal\', {
                        method: \'POST\',
                        credentials: \'same-origin\',
                        headers: {
                            \'sessionId\': \''.$sessionId.'\'
                            }
                    }).then(function(res) {
                            return res.json();
                    }).then(function(data) {
                            return data.result.id;
                    });
                },
                onError: function (err) {
                    $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Payment Error!</h2></div>\');
                    $(\'#alert-payment\').fadeIn("slow");
                },
                onApprove: function(data, actions) {
                    
                    $("#proses").hide();
                    $("#loader").addClass("loader");

                    actions.order.authorize().then(function(authorization) {
                            
                            var authorizationID = authorization.purchase_units[0].payments.authorizations[0].id
                            
                            $.ajax({
                                data: {
                                    "orderID": data.orderID,
                                    "authorizationID": authorizationID,
                                    "sessionId": \''.$sessionId.'\',
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/paypal/confirm\'
                            }).done(function(data) {
                                if(data.id=="1")
                                {
                                    $("#loader").hide();
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    window.openAppRoute(data.message); 
                                }
                                else
                                {
                                    $("#loader").hide();
                                    $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Payment Failed!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                }
                            }).fail(function(error) {
                                console.log(error);
                            });

                    });
                }
            }).render(\'#paypal-button-container\');
        });';
        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function webhook($webhook_app,Request $request)
    {
        if($webhook_app=="bokun")
        {
            $data = json_decode($request->getContent(), true);
            switch($request->input('action'))
            {
            case 'BOOKING_CONFIRMED':
                
                if(Shoppingcart::where('confirmation_code',$data['confirmationCode'])->count()==0)
                {
                    $shoppingcart = BookingHelper::webhook_insert_shoppingcart($data);
                    BookingHelper::shoppingcart_mail($shoppingcart);
                }
                return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
            break;
            case 'BOOKING_ITEM_CANCELLED':
                $shopping_cart = Shoppingcart::where('confirmation_code',$data['confirmationCode'])->firstOrFail();
                $shopping_cart->booking_status = "CANCELED";
                $shopping_cart->save();
                return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
            break;
            }
        }
        return response()->json([
                    "id" => "2",
                    "message" => 'Error'
                ]);
    }
    
}
