<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\PaymentHelper;
use budisteikul\toursdk\Helpers\ContentHelper;
use budisteikul\toursdk\Helpers\FirebaseHelper;
use budisteikul\toursdk\Helpers\GeneralHelper;
use budisteikul\toursdk\Helpers\LogHelper;
use budisteikul\toursdk\Helpers\SettingHelper;

use budisteikul\toursdk\Models\Category;
use budisteikul\toursdk\Models\Review;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Channel;
use budisteikul\toursdk\Models\Page;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartProduct;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;



class APIController extends Controller
{
    
    public function __construct(Request $request)
    {
        $this->currency = env("BOKUN_CURRENCY");
        $this->lang = env("BOKUN_LANG");
        $this->appAssetUrl = env("APP_ASSET_URL");
        $this->referer = $request->header('referer');
    }

    public function index_jscript(Request $request)
    {
        $paypal_sdk = 'https://www.paypal.com/sdk/js?client-id='.env("PAYPAL_CLIENT_ID").'&currency='. env("PAYPAL_CURRENCY").'';
        $payment_enable = SettingHelper::getSetting('payment_enable');
        $payment_array = explode(",",$payment_enable);
        $jscripts = [];

        if(in_array('paypal',$payment_array)) $jscripts[] = [$paypal_sdk, true];
        if(in_array('xendit',$payment_array)) {
            $jscripts[] = ['https://js.xendit.co/v1/xendit.min.js',false];
            $jscripts[] = [ env('APP_ASSET_URL') .'/js/payform.min.js',true];
        }
        if(in_array('stripe',$payment_array)) $jscripts[] = ['https://js.stripe.com/v3/', true];

        $analytic = LogHelper::analytic();

        return response()->json([
            'message' => 'success',
            'jscripts' => $jscripts,
            'analytic' => $analytic
        ], 200);
    }

    public function navbar($sessionId)
    {
        $categories = Category::where('parent_id',0)->get();
        $json_ld = self::json_ld();
        return response()->json([
            'message' => 'success',
            'json_ld' => $json_ld,
            'categories' => $categories,
            
        ], 200);
    }

    public function footer()
    {   
        
        $dataPrivacyTerm[] = [
            'title' => 'Terms and Conditions',
            'link' => '/page/terms-and-conditions'
        ];

        $dataPrivacyTerm[] = [
            'title' => 'Privacy Policy',
            'link' => '/page/privacy-policy'
        ];

        $usefullLink[] = [
            'title' => 'Night Walk Meeting Point',
            'link' => 'https://linktr.ee/foodtour',
            'type' => 'outsite'
        ];

        $usefullLink[] = [
            'title' => 'Morning Walk Meeting Point',
            'link' => 'https://linktr.ee/foodtour',
            'type' => 'outsite'
        ];

        $company = SettingHelper::getSetting('company');
        $footerTitle = SettingHelper::getSetting('footer');

        return response()->json([
            'message' => 'success',
            'address' => '',
            'usefullLinks' => $usefullLink,
            'whatsapp' => '+6285743112112',
            'privacyterms' => $dataPrivacyTerm,
            'company' => $company,
            'footerTitle' => $footerTitle,
            'partners' => [
                '<a target="_blank" rel="noreferrer noopener" href="https://www.getyourguide.com/yogyakarta-l349/yogyakarta-night-walking-and-food-tour-t429708"><img height="30" class="mb-1 mt-2 mr-2 img-thumbnail" src="'.$this->appAssetUrl.'/img/footer/getyourguide-logo.png"} alt="GetYourGuide" /></a>',
                '<a target="_blank" rel="noreferrer noopener" href="https://www.airbnb.com/experiences/434368"><img height="30" class="mb-1 mt-2 mr-2 img-thumbnail" src="'.$this->appAssetUrl.'/img/footer/airbnb-logo.png"} alt="Airbnb" /></a>',
                '<a target="_blank" rel="noreferrer noopener" href="https://www.tripadvisor.com/AttractionProductReview-g14782503-d15646790-Small_Group_Walking_and_Food_Tour_by_Night_in_Yogyakarta-Yogyakarta_Yogyakarta_R.html"><img height="30" class="mb-1 mt-2 mr-2 img-thumbnail" src="'.$this->appAssetUrl.'/img/footer/tripadvisor-logo.png"} alt="Tripadvisor" /></a>',
                
                
            ],
            'paymentChannels' => [
                '<img height="30" class="mt-2" src="'.$this->appAssetUrl.'/img/footer/line-1.png" alt="Payment Channels" /><br />',
                '<img height="30" class="mt-2" src="'.$this->appAssetUrl.'/img/footer/line-2.png" alt="Payment Channels" /><br />',
                '<img height="30" class="mt-2" src="'.$this->appAssetUrl.'/img/footer/line-4.png" alt="Payment Channels" /><br />',
            ]
        ], 200);
    }

    public function review_rate()
    {
        $rating = Review::sum('rating');
        $count = Review::count();
        if($count==0) $count = 1;

        $rate = $rating/$count;
        if ( strpos( $rate, "." ) !== false ) {
            $rate = number_format((float)$rate, 2, '.', '');
        }
        return $rate;
    }

    public function json_ld()
    {
        $rating = self::review_rate();
        $count = Review::count();
        $json = '
        {
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": "Yogyakarta Night Walking and Food Tours",
            "image": [
                "'.env("APP_ASSET_URL").'/img/schema/jogja-food-tour-1x1.jpg",
                "'.env("APP_ASSET_URL").'/img/schema/jogja-food-tour-4x3.jpg",
                "'.env("APP_ASSET_URL").'/img/schema/jogja-food-tour-16x9.jpg"
            ],
            "description": "See a different side of Yogyakarta, Indonesia’s cultural capital, on this fun night tour jam-packed with street food delights. Join your guide and no more than seven other travelers in the city center, then board a “becak” rickshaw to tour the sights. Savor the light, sweet flavors of Javanese cuisine; soak up the vibrant atmosphere of this university city; try traditional games; and enjoy fairground rides at Alun-Alun Kidul.",
            "sku": "110844P2",
            "mpn": "208273",
            "brand": {
                "@type": "Brand",
                "name": "JOGJA FOOD TOUR"
            },
            "review": {
                "@type": "Review",
                "reviewRating": {
                    "@type": "Rating",
                    "ratingValue": "'.$rating.'",
                    "bestRating": "5"
                },
                "author": {
                    "@type": "Person",
                    "name": "Travelers"
                }
            },
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "'.$rating.'",
                "reviewCount": "'.$count.'"
            },
            "offers": {
                "@type": "Offer",
                "url": "'.env("APP_URL").'/tour/yogyakarta-night-walking-and-food-tours",
                "priceCurrency": "IDR",
                "price": "575000",
                "priceValidUntil": "2023-12-31",
                "itemCondition": "https://schema.org/UsedCondition",
                "availability": "https://schema.org/InStock",
                "seller": {
                    "@type": "Organization",
                    "name": "'.env("APP_NAME").'"
                }
            }
        }';
        return json_encode(json_decode($json), JSON_UNESCAPED_SLASHES);
    }

    public function schedule_jscript()
    {
        $jscript = '
        jQuery(document).ready(function($) {
            $.fn.dataTable.ext.errMode = \'none\';  
            var table = $("#dataTables-example").DataTable(
            {
                "processing": true,
                "serverSide": true,
                "ajax": 
                {
                    "url": "'.url('/api').'/schedule",
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
                    {data: "name", name: "name", className: "auto", orderable: false},
                    {data: "date_text", name: "date_text", className: "auto", orderable: false},
                    {data: "people", name: "people", className: "auto", orderable: false},
                ],
                "dom": "tp",
                "pagingType": "full_numbers"
            });
            
      });';
      return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function schedule(Request $request)
    {
        $resources = ShoppingcartProduct::whereHas('shoppingcart', function ($query) {
                return $query->where('booking_status','CONFIRMED');
        })->where('date', '>=', date('Y-m-d'))->whereNotNull('date');
        return Datatables::eloquent($resources)
        ->addColumn('name', function($resources){
                    $shoppingcart_id = $resources->shoppingcart->id;
                    $question = BookingHelper::get_answer_contact($resources->shoppingcart);
                    $name = $question->firstName;
                    return $name;
                })
        ->addColumn('date_text', function($id){
                    $date_text = GeneralHelper::dateFormat($resources->date,10);
                    return $date_text;
                })
        ->addColumn('people', function($id){
                    $people = 0;
                    foreach($resources->shoppingcart_product_details as $shoppingcart_product_detail)
                    {
                        $people += $shoppingcart_product_detail->people;
                    }
                    return $people;
                })
        ->toJson();
    }

    

    public function review_count()
    {
        $rating = Review::sum('rating');
        $count = Review::count();

        $rate_count = $count;
        if($rate_count==0) $rate_count = 1;

        $rate = $rating/$rate_count;
        if ( strpos( $rate, "." ) !== false ) {
            $rate = number_format((float)$rate, 1, '.', '');
        }

        return response()->json([
            'message' => 'success',
            'count' => $count,
            'rate' => '('. $rate .')'
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

    

    public function downloadQrcode($sessionId,$id)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$id)->where('session_id',$sessionId)->firstOrFail();
        $qrcode = BookingHelper::generate_qrcode($shoppingcart);
        list($type, $qrcode) = explode(';', $qrcode);
        list(, $qrcode)      = explode(',', $qrcode);
        $qrcode = base64_decode($qrcode);
        $path = Storage::disk('local')->put($shoppingcart->confirmation_code .'.png', $qrcode);
        return response()->download(storage_path('app').'/'.$shoppingcart->confirmation_code .'.png')->deleteFileAfterSend(true);
    }

    public function instruction($sessionId,$id)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$id)->where('session_id',$sessionId)->firstOrFail();
        $pdf = BookingHelper::create_instruction_pdf($shoppingcart);
        return $pdf->download('Instruction-'. $shoppingcart->confirmation_code .'.pdf');
    }

    public function manual($sessionId,$id)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$id)->where('session_id',$sessionId)->firstOrFail();
        $pdf = BookingHelper::create_manual_pdf($shoppingcart);
        return $pdf->download('Manual-'. $shoppingcart->confirmation_code .'.pdf');
    }

    public function invoice($sessionId,$id)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$id)->where('session_id',$sessionId)->firstOrFail();
        $pdf = BookingHelper::create_invoice_pdf($shoppingcart);
        return $pdf->download('Invoice-'. $shoppingcart->confirmation_code .'.pdf');
    }
    
    public function ticket($sessionId,$id)
    {
        $shoppingcart_product = ShoppingcartProduct::where('product_confirmation_code',$id)->whereHas('shoppingcart', function($query) use ($sessionId){
            return $query->where('session_id', $sessionId)->where('booking_status','CONFIRMED');
        })->firstOrFail();
        $pdf = BookingHelper::create_ticket_pdf($shoppingcart_product);
        return $pdf->download('Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf');
    }

    
    public function home()
    {
        //$dataObj = ContentHelper::view_categories();
        $category = Category::where('slug','jogja-food-tour')->firstOrFail();
        $dataObj = ContentHelper::view_category($category);
        return response()->json([
            'message' => 'success',
            'categories' => $dataObj
        ], 200);
        
    }

 
    public function categories()
    {
        $dataObj = ContentHelper::view_categories();
        //$category = Category::where('slug','jogja-food-tour')->firstOrFail();
        //$dataObj = ContentHelper::view_category($category);
        return response()->json([
            'message' => 'success',
            'categories' => $dataObj
        ], 200);
        
    }

    public function category($slug)
    {
        $category = Category::where('slug',$slug)->firstOrFail();
        $dataObj = ContentHelper::view_category($category);
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
        $dataObj = ContentHelper::view_product($product);
        return response()->json([
            'message' => 'success',
            'product' => $dataObj,
        ], 200);

    }



    public function review_jscript()
    {
        $jscript = '
        jQuery(document).ready(function($) {
            $.fn.dataTable.ext.errMode = \'none\';
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
                    
                    if($resource->title!="")
                    {
                        $title = '<b>'.$resource->title.'</b><br>';
                    }
                    else
                    {
                        $title = '';
                    }
                    
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
                    //$output = $user.$post_title.$rating.$title.$text;
                    //$output = $user.$post_title.$title.$text;
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

        
        FirebaseHelper::shoppingcart($sessionId);

        return response()->json($contents);
    }

    public function shoppingcart(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $sessionId = $data['sessionId'];

        $shoppingcart = Cache::get('_'. $sessionId);

        if(!isset($shoppingcart->products))
        {
            return array();
        }

        if(count($shoppingcart->products)==0)
        {
            return array();
        }
        


        $dataShoppingcart = ContentHelper::view_shoppingcart($shoppingcart);
        FirebaseHelper::shoppingcart($sessionId);
        

        return response()->json([
            'message' => 'success',
            'shoppingcarts' => $dataShoppingcart,
        ], 200);
    }
    
    public function product_remove(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        Cache::forget('_bokunProductById_'. $this->currency .'_'. $this->lang .'_'.$data);

        $sessionId = $data['sessionId'];
        
        FirebaseHelper::shoppingcart($sessionId);

        return response()->json([
                'message' => 'success'
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
        
        FirebaseHelper::shoppingcart($sessionId);

        return response()->json([
            "message" => "success"
        ]);
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

        $status = BookingHelper::apply_promocode($sessionId,trim($promocode));

        FirebaseHelper::shoppingcart($sessionId);

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
        
        
        FirebaseHelper::shoppingcart($sessionId);

        return response()->json([
                'message' => 'success'
            ], 200);
    }

    

    public function snippetsinvoice(Request $request)
    {
        $contents = BokunHelper::get_invoice(json_decode($request->getContent(), true));
        return response()->json($contents);
    }

    /*
    public function check_seat($date,$time)
    {
        $seat = 0;
        $aaa = self::raillink(6);
        
        foreach($aaa as $bbb)
        {
            if($bbb['date']==$date)
            {
                if($bbb['departure']==$time)
                {
                    $seat = $bbb["seat"];
                }
                
            }
        }
        
        return $seat;
    }

    public function date_raillink($range=6)
    {

        for($i=0;$i<=$range;$i++)
        {
            $date = strtotime(date('Y-m-d'));
            $date = strtotime("+".$i." day", $date);
            $value[] = date('Y-m-d', $date);
        }

        return $value;
    }

    public function raillink($range)
    {
        $raillink = [];
        for($i=0;$i<=$range;$i++)
        {
            $date = strtotime(date('Y-m-d'));
            $date = strtotime("+".$i." day", $date);
            $date = date('Y-m-d', $date);
            
            $aaa = self::check_raillink("YK","YIA",$date);
            if($aaa!="")
            {
                $raillink[] = $aaa;
            }

            $aaa = self::check_raillink("YIA","YK",$date);
            if($aaa!="")
            {
                $raillink[] = $aaa;
            }

        }
        
        $value = [];
        foreach($raillink as $bbb)
        {
            foreach($bbb as $ccc)
            {
                $value[] = $ccc;
            }
        }
        return $value;
    }


    public function check_raillink($org,$des,$date)
    {
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiSUJPT0siLCJjcmVhdGVkb24iOiIyMDE5LTExLTE0IDEzOjA4OjQ2In0.usU2bJ0H4RiDTaFnl7eaCsHgd07sVleaoSTTpF0glvg';
        

        $value = Cache::remember('_raillink_'. $org .'_'. $des .'_'. $date,7200, function() use ($org,$des,$date,$token)
        {
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_URL, "https://reservation.railink.co.id:8001/api/service/artsmidapp/middleware/schedule/arts_getschedule?org=".$org."&des=".$des."&date=".$date);

            $headerArray[] = "Token: ". $token;

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

            $response = curl_exec($ch);
            
            curl_close ($ch);
            
            return $response;
        });

        $response = json_decode($value);

        $dataKa = null;
        
        if($response->status!=10)
        {
            foreach($response->response->availabilitydatalist as $a)
            {
                foreach($a->scheduleDatas as $b)
                {
                    $departure  = substr($b->stopdeparture,0,2) .":". substr($b->stopdeparture,-2);
                    foreach($b->allocationDatas as $c)
                    {
                        $dataKa[] = array(
                            'org' => $org,
                            'des' => $des,
                            'date' => $date,
                            'departure' => $departure,
                            'seat' => $c->seatavailable,
                        );
                    }
                
                }
            }
        }
        
        return $dataKa;
    }
    */
    
    public function snippetscalendar($activityId,$year,$month)
    {
        $contents = BookingHelper::get_calendar($activityId,$year,$month);

        // Railink
        //=============================================================================
        /*
        if($activityId==10786) {
        if(isset($contents->firstAvailableDay->fullDate)){
        $date_raillink = self::date_raillink();
        if (in_array($contents->firstAvailableDay->fullDate, $date_raillink))
        { 
                
                $z = 0;
                $totalseat = 0;
                foreach($contents->firstAvailableDay->availabilities as $availability)
                {
                    $seat = self::check_seat($contents->firstAvailableDay->fullDate,$availability->data->startTime);

                    
                    $contents->firstAvailableDay->availabilities[$z]->data->startTimeLabel = $contents->firstAvailableDay->availabilities[$z]->data->startTimeLabel. ' (' . $seat .' Seat)';
                    $contents->firstAvailableDay->availabilities[$z]->activityAvailability->startTimeLabel = $contents->firstAvailableDay->availabilities[$z]->activityAvailability->startTimeLabel. ' (' . $seat .' Seat)';

                    if($seat<1)
                    {
                        unset($contents->firstAvailableDay->availabilities[$z]);
                        
                    }
                    $z++;
                    $totalseat = $totalseat + $seat;
                }
                if($totalseat==0) $contents->firstAvailableDay->available = false;

                $contents->firstAvailableDay->availabilities = array_values($contents->firstAvailableDay->availabilities);
        }

        //=============================================================================
        
        foreach($contents->weeks as $week)
        {
            foreach($week->days as $day)
            {
                if (in_array($day->fullDate, $date_raillink))
                {
                    
                    $z = 0;
                    $totalseat = 0;
                    foreach($day->availabilities as $availability)
                    {

                        $seat = self::check_seat($day->fullDate,$availability->data->startTime);

                        $day->availabilities[$z]->data->startTimeLabel = $day->availabilities[$z]->data->startTimeLabel. ' (' . $seat .' Seat)';
                        $day->availabilities[$z]->activityAvailability->startTimeLabel = $day->availabilities[$z]->activityAvailability->startTimeLabel. ' (' . $seat .' Seat)';

                        if($seat<1)
                        {
                            unset($day->availabilities[$z]);
                            
                        }

                        $z++;
                        $totalseat = $totalseat + $seat;
                    }
                    if($totalseat==0) $day->available = false;

                    $day->availabilities = array_values($day->availabilities);
                }
                else
                {
                    $day->available = false;
                }
            }
        }}}
        */
        //=============================================================================
        // Railink

        return response()->json($contents);
    }

    public function product_jscript($slug,$sessionId,Request $request)
    {
        $embedded = $request->input('embedded');
        $product = Product::where('slug',$slug)->first();
        if($product)
        {
            $content = BokunHelper::get_product($product->bokun_id);
            $calendar = BokunHelper::get_calendar_new($content->id);

            $availability = BookingHelper::get_firstAvailability($content->id,$calendar->year,$calendar->month);
            
            // Railink
            //=============================================================================
            /*
            if($product->bokun_id==10786) {

                $date_raillink = self::date_raillink();
                $mil = $availability[0]['date'];
                $seconds = $mil / 1000;
                $fullDate = date("Y-m-d", $seconds);

                if (in_array($fullDate, $date_raillink))
                {
                    
                    $z = 0;
                    $totalseat = 0;
                    foreach($availability[0]['availabilities'] as $aaa)
                    {
                        $seat = self::check_seat($fullDate,$aaa->startTime);
                        $aaa->startTimeLabel = $aaa->startTimeLabel . ' (' . $seat .' Seat)';

                        if($seat<1)
                        {
                            unset($availability[0]['availabilities'][$z]);
                        }
                        $z++;
                        $totalseat = $totalseat + $seat;
                    }
                    
                    if($totalseat==0) $availability[0]['available'] = false;

                    $availability[0]['availabilities'] = array_values($availability[0]['availabilities']);
                }
            }
            */
            //=============================================================================
            // Railink

            $microtime = $availability[0]['date'];
            $month = date("n",$microtime/1000);
            $year = date("Y",$microtime/1000);

            if($embedded=="") $embedded = "true";

            

            
            $jscript = ' 
            
            

            window.priceFormatter = new WidgetUtils.PriceFormatter({
                currency: \''. $this->currency .'\',
                language: \''. $this->lang .'\',
                decimalSeparator: \'.\',
                groupingSeparator: \',\',
                symbol: \''. $this->currency .' \'
            });

            

            window.i18nLang = \''. $this->lang .'\';

            

            try { 
                $("#titleProduct").append(\''. $product->name .'\');
            } catch(err) {  
            }

            try { 
                $("#titleBooking").html(\'Booking '. $product->name .'\');
            } catch(err) {  
            }

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
                onAfterRender: function(selectedDate) {
                    
                    $(".PICK_UP").hide();
                    $("#proses").remove();
                },
                onAvailabilitySelected: function(selectedRate, selectedDate, selectedAvailability) {
                },
                onAddedToCart: function(cart) {
                    
                    window.openAppRoute(\'/booking/checkout\');
                },
        
                calendarMonth: '.$month.',
                calendarYear: '.$year.',
                loadingCalendar: true,
        
                activity: '.json_encode($content).',
        
                upcomingAvailabilities: [],
        
                firstDayAvailabilities: '.json_encode($availability).'
            };
            
            if($("#ActivityBookingWidget").parent().length==0)
            {
                window.reloadJscript();
            }
            

            ';   
        }
        else
        {
            $jscript = 'window.openAppRoute(\'/page/not/found\')'; 
        }

        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function last_order($sessionId)
    {
        $shoppingcarts = Shoppingcart::with('shoppingcart_products')->WhereHas('shoppingcart_products', function($query) {
                 $query->where('date','>=',date('Y-m-d 00:00:00'));
            })->where('session_id', $sessionId)->orderBy('id','desc')->get();
        
        if($shoppingcarts->isEmpty())
        {
            return response()->json([
                'message' => 'success',
                'booking' => array()
            ], 200);
        }
        
        $booking = ContentHelper::view_last_order($shoppingcarts);
        
        return response()->json([
                'message' => 'success',
                'booking' => $booking
            ], 200);
        
    }

    public function receipt($sessionId,$confirmationCode)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$confirmationCode)->where('session_id', $sessionId)->where(function($query){
            return $query->where('booking_status', 'CONFIRMED')
                         ->orWhere('booking_status', 'CANCELED')
                         ->orWhere('booking_status', 'PENDING');
        })->firstOrFail();

        if(!isset($shoppingcart->shoppingcart_payment))
        {
            abort(404);
        }
        
        BookingHelper::booking_expired($shoppingcart);

        $dataObj = ContentHelper::view_receipt($shoppingcart);

        FirebaseHelper::receipt($shoppingcart);
        
        return response()->json([
                'receipt' => $dataObj,
                'message' => "success"
            ], 200);
    }
    


    

    

    

    

    

    public function checkout_jscript()
    {
        $jscript = '
        
        function reloadThisPage()
        {
            window.location.reload();
        }

        function clearFormAlert(data)
        {
            $.each(data, function( index, value ) {
                $(\'#\'+ value).removeClass(\'is-invalid\');
                $(\'#span-\'+ value).remove();
            });
        }

        function formAlert(data)
        {
            $.each( data, function( index, value ) {
            $(\'#\'+ index).addClass(\'is-invalid\');
                if(value!="")
                {
                    $(\'#\'+ index).after(\'<span id="span-\'+ index  +\'" class="invalid-feedback" role="alert"><strong>\'+ value +\'</strong></span>\');
                }
            });
            
        }

        function showAlert(string,status)
        {
            if(status=="show")
            {
                $(\'#info-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> \'+ string +\'</h2></div>\');
                $(\'#info-payment\').fadeIn("slow");
            }
            else
            {
                $("#info-payment").slideUp("slow");
            }
        }

        function failedpaymentEwallet(ewallet)
            {
                if(ewallet=="ovo")
                {
                    $("#text-alert").hide();
                    $("#text-alert").html( "" );

                    $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Transaction failed</h2></div>\');
                    $(\'#alert-payment\').fadeIn("slow");
                    $("#ovoPhoneNumber").attr("disabled", false);
                    $("#submit").attr("disabled", false);
                    $("#submit").html(\' <strong>Click to pay with <img class="ml-2 mr-2" src="'.$this->appAssetUrl.'/img/payment/ovo-light.png" height="30" /></strong> \');
                }
            }

        function submitDisabled()
        {
            $("#submitCheckout").attr("disabled", true);
            $(\'#submitCheckout\').html(\'<i class="fa fa-spinner fa-spin"></i>&nbsp;&nbsp;processing...\');
        }

        function submitEnabled()
        {
            $("#submitCheckout").attr("disabled", false);
            $(\'#submitCheckout\').html(\'<i class="fas fa-lock"></i> <strong id="submitText"></strong>\');
        }

        function redirect(url)
        {
            $(\'#submitCheckout\').html(\'<i class="fa fa-spinner fa-spin"></i>&nbsp;&nbsp;redirecting...\');
            setTimeout(function (){
                window.location.href = url;
            }, 1000);
        }

        function showButton(deeplink,name)
        {
            $("#submitCheckout").slideUp("slow");
            $("#paymentContainer").html(\'<a class="btn btn-lg btn-block btn-theme" href="\'+ deeplink +\'"><strong>Click to pay with \'+ name +\'</strong></a>\');
        }

        ';
        
        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function receipt_jscript()
    {
        $jscript = '
            
            function clear_timer()
            {
                clearInterval(document.getElementById("timer_id").value);
            }

            function payment_timer(due_date,session_id,confirmation_code)
            {
                 clearInterval(document.getElementById("timer_id").value);

                 var x = {};
                 var countDownDate = new Date(due_date).getTime();
                 x[due_date] = setInterval(function() {

                    
                    var now = new Date().getTime();
                    var distance = countDownDate - now;

                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    if(days>0)
                    {
                        try
                        {
                            document.getElementById("payment_timer").innerHTML = days + " day " + hours + " hrs "
      + minutes + " min " + seconds + " sec ";
                        }
                        catch(e)
                        {

                        }
                        
                    }
                    else if(hours>0)
                    {
                        try
                        {
                            document.getElementById("payment_timer").innerHTML = hours + " hrs "
      + minutes + " min " + seconds + " sec ";
                        }
                        catch(e)
                        {

                        }
                        
                    }
                    else
                    {
                        try
                        {
                            document.getElementById("payment_timer").innerHTML = minutes + " min " + seconds + " sec ";
                        }
                        catch(e)
                        {

                        }
                        
                    }
                    

                    document.getElementById("timer_id").value = x[due_date];
                    if (distance < 0) {
                        clearInterval(x[due_date]);
                        document.getElementById("payment_timer").innerHTML = "Payment expired";
                        $.get("'.url('/api').'/receipt/"+session_id+"/"+confirmation_code);
                    }

                }, 1000);

                
            }';

        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    

    

    
    
}
