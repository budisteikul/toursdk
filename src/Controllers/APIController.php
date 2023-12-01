<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\ContentHelper;
use budisteikul\toursdk\Helpers\FirebaseHelper;
use budisteikul\toursdk\Helpers\GeneralHelper;
use budisteikul\toursdk\Helpers\VoucherHelper;
use budisteikul\toursdk\Helpers\LogHelper;
use budisteikul\toursdk\Helpers\WiseHelper;
use budisteikul\toursdk\Helpers\PaypalHelper;
use budisteikul\toursdk\Helpers\XenditHelper;
use budisteikul\toursdk\Helpers\SettingHelper;

use budisteikul\toursdk\Models\Category;
use budisteikul\toursdk\Models\Review;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Voucher;
use budisteikul\toursdk\Models\Channel;
use budisteikul\toursdk\Models\Page;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartPayment;
use budisteikul\toursdk\Models\ShoppingcartProduct;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;


class APIController extends Controller
{
    
    

    public function __construct(Request $request)
    {
        $this->currency = env("BOKUN_CURRENCY");
        $this->lang = env("BOKUN_LANG");
        $this->appAssetUrl = env("APP_ASSET_URL");
        $this->referer = $request->header('referer');
    }

    public function index_jscript()
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

        $address = '<strong>VERTIKAL TRIP</strong><br />
                    Perum. Guwosari Blok XII<br />
                    Jalan Abiyoso VII No.190<br />
                    Bantul, D.I Yogyakarta 55751<br />
                    <i class="fas fa-envelope"></i> guide@vertikaltrip.com<br />
                    <i class="fas fa-phone-square-alt"></i> +62 857 4311 2112<br />';

        $company = SettingHelper::getSetting('company');
        $footerTitle = SettingHelper::getSetting('footer');

        return response()->json([
            'message' => 'success',
            'address' => $address,
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
                '<img height="30" class="mt-2" src="'.$this->appAssetUrl.'/img/footer/payment-channel.png" alt="Payment Channels" /><br />',
                '<img height="30" class="mt-2" src="'.$this->appAssetUrl.'/img/footer/line-2.png" alt="Payment Channels" /><br />',
                //'<img height="30" class="mt-2" src="'.$this->appAssetUrl.'/img/footer/line-4.png" alt="Payment Channels" /><br />',
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
            if(count($check_question) > 0)
            {
                $check_question['message'] = "Oops there was a problem, please check your input and try again.";
                return response()->json($check_question);
            }
            $shoppingcart = BookingHelper::save_question_json($sessionId,$data);
            
            FirebaseHelper::shoppingcart($sessionId);
            
            $payment = $data['payment'];

            switch($payment)
            {
                case 'paypal':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'paypal',
                        'id' => 3
                    ], 200);
                break;

                case 'stripe':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'stripe',
                        'id' => 3
                    ]);
                break;

                case 'xendit':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'xendit',
                        'id' => 3
                    ]);
                break;

                case 'ovo':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'ovo',
                        'id' => 3
                    ]);
                break;

                case 'qris':
                    //VoucherHelper::apply_voucher($sessionId,'LOCALPAYMENT');
                    BookingHelper::set_bookingStatus($sessionId,'PENDING');
                    BookingHelper::set_confirmationCode($sessionId);
                    $response = BookingHelper::create_payment($sessionId,"xendit","qris");
                break;

                case 'dana':
                    BookingHelper::set_bookingStatus($sessionId,'PENDING');
                    BookingHelper::set_confirmationCode($sessionId);
                    $response = BookingHelper::create_payment($sessionId,"xendit","dana");
                break;

                case 'bss':
                    BookingHelper::set_bookingStatus($sessionId,'PENDING');
                    BookingHelper::set_confirmationCode($sessionId);
                    $response = BookingHelper::create_payment($sessionId,"xendit","bss");
                break;

                default:
                    BookingHelper::set_bookingStatus($sessionId,'PENDING');
                    BookingHelper::set_confirmationCode($sessionId);
                    $response = BookingHelper::create_payment($sessionId,"xendit","invoice");

            }

            if($response->status->id=="0")
            {
                return response()->json([
                    'id' => "0",
                    'message' => $response->status->message,
                ]);
            }

            $shoppingcart = BookingHelper::confirm_booking($sessionId);
            
            $text = null;
            $session_id = $shoppingcart->session_id;
            $confirmation_code = $shoppingcart->confirmation_code;
            $redirect_type = 1;
            $redirect = $shoppingcart->shoppingcart_payment->redirect;

            if($shoppingcart->shoppingcart_payment->payment_type=="ewallet")
            {
                $redirect_type = 2;
                $text = strtoupper($shoppingcart->shoppingcart_payment->bank_name);
            }

            if($shoppingcart->shoppingcart_payment->payment_type=="bank_redirect")
            {
                $redirect_type = 4;
            }


            return response()->json([
                "message" => "success",
                "id" => $redirect_type,
                "redirect" => $redirect,
                "text" => $text,
                "session_id" => $session_id,
                "confirmation_code" => $confirmation_code
            ]);
            
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
    


    public function confirmpaymentstripe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => ['required', 'string', 'max:255'],
            'authorizationID' => ['required', 'string', 'max:255'],
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $sessionId = $request->input('sessionId');
        $authorizationID = $request->input('authorizationID');
        $shoppingcart = Cache::get('_'. $sessionId);

        if($shoppingcart->payment->authorization_id!=$authorizationID)
        {
            return response()->json([
                    "id" => "2",
                    "message" => 'Error'
                ]);
        }
        
        $shoppingcart->payment->payment_status = 2;
        
        Cache::forget('_'. $sessionId);
        Cache::add('_'. $sessionId, $shoppingcart, 172800);

        BookingHelper::set_bookingStatus($sessionId,'CONFIRMED');
        $shoppingcart = BookingHelper::confirm_booking($sessionId);

        return response()->json([
                    "id" => "1",
                    "message" => "/booking/receipt/".$shoppingcart->session_id."/".$shoppingcart->confirmation_code
                ]);
    }

    public function confirmpaymentpaypal(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'orderID' => ['required', 'string', 'max:255'],
                'sessionId' => ['required', 'string', 'max:255'],
            ]);
        
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json($errors);
            }
        
            $orderID = $request->input('orderID');
            $sessionId = $request->input('sessionId');
        
            $shoppingcart = Cache::get('_'. $sessionId);

            $shoppingcart->payment->authorization_id = PaypalHelper::getCaptureId($orderID);;
            $shoppingcart->payment->payment_status = 2;
        
            Cache::forget('_'. $sessionId);
            Cache::add('_'. $sessionId, $shoppingcart, 172800);
        
            BookingHelper::set_bookingStatus($sessionId,'CONFIRMED');

            $shoppingcart = BookingHelper::confirm_booking($sessionId);

            return response()->json([
                    "id" => "1",
                    "message" => "/booking/receipt/".$shoppingcart->session_id."/".$shoppingcart->confirmation_code
                ]);
    }

    public function createpaymentpaypal(Request $request)
    {
            $sessionId = $request->header('sessionId');
            BookingHelper::set_confirmationCode($sessionId);
            $response = BookingHelper::create_payment($sessionId,"paypal");
            return response()->json($response->data);
    }

    public function createpaymentstripe(Request $request)
    {
            $sessionId = $request->header('sessionId');
            BookingHelper::set_confirmationCode($sessionId);
            $response = BookingHelper::create_payment($sessionId,"stripe");
            return response()->json($response->data);
    }

    public function createpaymentxendit(Request $request)
    {
            $sessionId = $request->header('sessionId');
            $tokenId = $request->header('tokenId');
            BookingHelper::set_confirmationCode($sessionId);
            $response = BookingHelper::create_payment($sessionId,"xendit","card",$tokenId);
            
            if($response->status->id==1)
            {
                BookingHelper::set_bookingStatus($sessionId,'CONFIRMED');
                $shoppingcart = BookingHelper::confirm_booking($sessionId);
                return response()->json([
                    "id" => "1",
                    "message" => "/booking/receipt/".$shoppingcart->session_id."/".$shoppingcart->confirmation_code
                ]);
            }
            else
            {
                return response()->json([
                    "id" => "0",
                    "message" => $response->status->message
                ]);
            }
            
    }

    public function confirmpaymentxendit(Request $request)
    {
        $value = $request->header('x-callback-token');
        if(env('XENDIT_CALLBACK_TOKEN')!=$value)
        {
            return response()->json([
                'message' => "ERROR"
            ], 200);
        }

        $data = $request->all();
        
        if(isset($data['external_id']))
        {
            $external_id = $data['external_id'];
            $shoppingcart_payment = ShoppingcartPayment::where('payment_provider','xendit')->where('order_id',$external_id)->first();
            if($shoppingcart_payment){
                BookingHelper::confirm_payment($shoppingcart_payment->shoppingcart,"CONFIRMED");
                BookingHelper::shoppingcart_notif($shoppingcart_payment->shoppingcart);
            }

            return response()->json([
                'message' => "success"
            ], 200);
        }

        $event = $data['event'];
        $channel_code = $data['data']['channel_code'];
        $reference_id = $data['data']['reference_id'];

        if($reference_id=="test-payload")
        {
            return response()->json([
                'message' => "TEST OK"
            ], 200);
        }

        if($reference_id=="testing_id_123")
        {
            return response()->json([
                'message' => "TEST OK"
            ], 200);
        }

        if($event=="ewallet.capture")
        {
            if($channel_code=="ID_OVO")
            {
                $output = FirebaseHelper::read_payment($reference_id);
                $sessionId = $output->session_id;

                if($data['data']['status']!="SUCCEEDED")
                {
                    FirebaseHelper::upload_payment('FAILED',$reference_id,$sessionId,'ovo');
                    return response()->json([
                        'message' => "error"
                    ], 200);
                }

                //VoucherHelper::apply_voucher($sessionId,'LOCALPAYMENT');
                BookingHelper::set_bookingStatus($sessionId,'CONFIRMED');
                BookingHelper::set_confirmationCode($sessionId);
                BookingHelper::create_payment($sessionId,"xendit","ovo");
                $shoppingcart = BookingHelper::confirm_booking($sessionId);
                FirebaseHelper::upload_payment('CONFIRMED',$reference_id,$sessionId,'ovo',"/booking/receipt/".$shoppingcart->session_id."/".$shoppingcart->confirmation_code);
                BookingHelper::shoppingcart_notif($shoppingcart_payment->shoppingcart);
            }

            if($channel_code=="ID_DANA")
            {
                $shoppingcart_payment = ShoppingcartPayment::where('payment_provider','xendit')->where('order_id',$reference_id)->first();
                if($shoppingcart_payment){
                    BookingHelper::confirm_payment($shoppingcart_payment->shoppingcart,"CONFIRMED");
                    BookingHelper::shoppingcart_notif($shoppingcart_payment->shoppingcart);
                }
            }
        }

        if($event=="qr.payment")
        {
                $shoppingcart_payment = ShoppingcartPayment::where('payment_provider','xendit')->where('order_id',$reference_id)->first();
                if($shoppingcart_payment){
                    BookingHelper::confirm_payment($shoppingcart_payment->shoppingcart,"CONFIRMED");
                    BookingHelper::shoppingcart_notif($shoppingcart_payment->shoppingcart);
                    $shoppingcart_payment->authorization_id = $data['data']['id'];
                    $shoppingcart_payment->save();
                }
        }
        

        return response()->json([
                'message' => "success"
            ], 200);
    }

    public function createpaymentovo(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'sessionId' => ['required', 'string', 'max:255'],
                'phoneNumber' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'required',
                ]);
            }

            $phoneNumber = $request->input('phoneNumber');
            $sessionId = $request->input('sessionId');

            $shoppingcart = Cache::get('_'. $sessionId);
            
            // Xendit ========================================================================
            
            if(substr($phoneNumber,0,1)=="0")
            {
                $phoneNumber = substr($phoneNumber,1);
            }
            $phoneNumber = "+62". $phoneNumber;

            $xendit = new XenditHelper();
            $response = $xendit->createEWalletOvoCharge($shoppingcart->due_now,$phoneNumber);
            if(isset($response->error_code))
            {
                return response()->json([
                    "message" => "error"
                ]);
            }
            FirebaseHelper::upload_payment('PENDING',$response->reference_id,$sessionId,'ovo');
            return response()->json([
                    "message" => "success",
                    "reference_id" => $response->reference_id
                ]);
            
    }

    public function ovo_jscript($sessionId)
    {
        $shoppingcart = Cache::get('_'. $sessionId);
        $jscript = '
            $("#submitCheckout").slideUp("slow");
            $("#paymentContainer").html(\'<div class="form mb-2 mt-2"><strong>Please input your OVO number :</strong></div><div class="form-row mb-4 mt-2"><div class="col-xs-2"><input type="text" style="height:47px; width:50px;" class="form-control  disabled" value="+62" disabled></div><div class="col"><input id="ovoPhoneNumber" type="text" style="height:47px;" class="form-control" placeholder="85743112112"></div></div><div id=\"text-alert\" class=\"text-center mb-4 mt-2\"></div><button id="submit" onClick="createpaymentovo()" class="btn btn-lg btn-block btn-theme" style="height:47px"><strong>Click to pay with <img class="ml-2 mr-2" src="'.$this->appAssetUrl.'/img/payment/ovo-light.png" height="30" /></strong></button>\');

            

            

            function createpaymentovo()
            {
                            var phoneNumber = document.getElementById("ovoPhoneNumber").value;
                            
                            $("#ovoPhoneNumber").removeClass("is-invalid");
                            $("#alert-payment").slideUp("slow");
                            $("#ovoPhoneNumber").attr("disabled", true);
                            $("#submit").attr("disabled", true);
                            $("#submit").html(\' <i class="fa fa-spinner fa-spin fa-fw"></i>  processing... \');

                            $("#text-alert").show();
                            $("#text-alert").html( "Please check OVO app on your mobile phone, to process payment" );
                            
                            if(phoneNumber=="")
                            {
                                $("#ovoPhoneNumber").addClass("is-invalid");
                                
                                $("#text-alert").hide();
                                $("#text-alert").html( "" );
                                $("#ovoPhoneNumber").attr("disabled", false);
                                $("#submit").attr("disabled", false);
                                $("#submit").html(\' <strong>Click to pay with <img class="ml-2 mr-2" src="'.$this->appAssetUrl.'/img/payment/ovo-light.png" height="30" /></strong> \');
                                return false;
                            }


                            $.ajax({
                                data: {
                                    "sessionId": \''.$sessionId.'\',
                                    "phoneNumber": phoneNumber,
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/ovo\'
                                }).done(function(data) {
                                    
                                    if(data.message=="success")
                                    {
                                        window.startListenerEwallet(data.reference_id);
                                        setTimeout(
                                        function() 
                                        {
                                            window.stopListenerEwallet(data.reference_id);
                                            failedpaymentEwallet("ovo");
                                        }, 65000);
                                    }
                                    else
                                    {
                                        failedpaymentEwallet("ovo");
                                    }

                                }).fail(function(error) {

                                    failedpaymentEwallet("ovo");
                                        
                            });

                            return false;
            }
        ';
        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function xendit_jscript($sessionId)
    {
        $shoppingcart = Cache::get('_'. $sessionId);
        $amount = BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,'IDR');
        
        $jscript = '

        $("#submitCheckout").slideUp("slow");

        $("#paymentContainer").html(\'<form id="payment-form"><div class="row mt-4"><div class="col-md-12 mb-2"><strong>Card Information</strong></div><div class="col-md-12 mb-2"><div class="input-group"><div class="input-group-append"><span class="input-group-text" id="inputGroupPrepend3"><i id="cardBrand" class="far fa-credit-card"></i></span></div><input class="form-control" type="text" id="card-number" placeholder="Card Number" value="" style="height: 47px;"><div id="cardNumberFeddback" class="invalid-feedback">Card number invalid.</div></div></div></div><div class="row"><div class="col-md-6 mb-2"><input type="text" class="form-control" id="cc-expiration" placeholder="MM / YY" required="" style="height: 47px;"><div id="expirationFeddback" class="invalid-feedback">Expiration invalid.</div></div><div class="col-md-6 mb-2"><input type="text" class="form-control" id="cc-cvv" placeholder="3-4 digits CVV / CVC" required="" style="height: 47px;"><div id="cvvFeddback" class="invalid-feedback">CVV / CVC invalid.</div></div></div><button style="height:47px;" class="mt-2 btn btn-lg btn-block btn-theme" id="submit"><strong>Pay with card</strong></button></form><div id=\"loader\" class=\"mb-4\"></div><div id=\"text-alert\" class=\"text-center\"></div><div id="three-ds-container" class="modal" style="display: none;"></div>\');

        payform.cardNumberInput(document.getElementById("card-number"));
        payform.expiryInput(document.getElementById("cc-expiration"));
        payform.cvcInput(document.getElementById("cc-cvv"));

        $(\'#card-number\').on(\'input\', function() {
            if($(\'#card-number\').val().length >=3)
            {
                var card_brand = payform.parseCardType($(\'#card-number\').val());
                if(card_brand=="visa")
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'fab\').addClass(\'fa-cc-visa\');
                }
                else if(card_brand=="mastercard")
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'fab\').addClass(\'fa-cc-mastercard\');
                }
                else if(card_brand=="jcb")
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'fab\').addClass(\'fa-cc-jcb\');
                }
                else
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'far\').addClass(\'fa-credit-card\');
                }
            }
            
            
        });

        function xenditResponseHandler (err, creditCardToken) {
            if (creditCardToken.status === "APPROVED" || creditCardToken.status === "VERIFIED") {
                            $("#three-ds-container").hide();
                            $("#loader").show();
                            $("#payment-form").slideUp("slow");  
                            $("#proses").hide();
                            $("#loader").addClass("loader");
                            $("#text-alert").show();
                            $("#text-alert").prepend( "Please wait and do not close the browser or refresh the page" );
                            $.ajax({
                                beforeSend: function(request) {
                                    request.setRequestHeader(\'sessionId\', \''. $shoppingcart->session_id .'\');
                                    request.setRequestHeader(\'tokenId\', creditCardToken.id);
                                },
                                type: \'POST\',
                                url: \''. env('APP_API_URL') .'/payment/xendit\'
                            }).done(function( data ) {
                                if(data.id=="1")
                                {
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000);
                                    
                                }
                                else
                                {
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $("#payment-form").slideDown("slow");
                                    enableButton();
                                    $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><strong style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> \'+ data.message +\'</strong></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                }
                            });
            } else if (creditCardToken.status === "IN_REVIEW") {
                            $("#three-ds-container").hide();
                            $("#three-ds-container").html("<iframe id=\"3ds-inline-frame\" name=\"3ds-inline-frame\" scrolling=\"no\"></iframe>");
                            $("#3ds-inline-frame").css("background-color", "#FFFFFF");
                            $("#3ds-inline-frame").css("top", "0px");
                            $("#3ds-inline-frame").css("left", "0px");
                            $("#3ds-inline-frame").css("width", "100%");
                            $("#3ds-inline-frame").css("height", "100%");
                            $("#3ds-inline-frame").css("position", "absolute");
                            window.open(creditCardToken.payer_authentication_url, "3ds-inline-frame");
                            $("#three-ds-container").show();
            } else if (creditCardToken.status === "FRAUD") {
                            enableButton();
            } else if (creditCardToken.status === "FAILED") {
                            enableButton();
            }
        }
        
        function cleanFeedback()
        {
            $("#card-number").removeClass("is-invalid");
            $("#cc-expiration").removeClass("is-invalid");
            $("#cc-cvv").removeClass("is-invalid");
        }

        function enableButton()
        {
            $("#three-ds-container").hide();
            $("#card-number").attr("disabled", false);
            $("#cc-expiration").attr("disabled", false);
            $("#cc-cvv").attr("disabled", false);
            $("#loader").hide();
            $("#loader").removeClass("loader");
            $("#payment-form").slideDown("slow");
            $("#submit").attr("disabled", false);
            $("#submit").html(\'<strong>Pay with card</strong>\');
        }

        var form = document.getElementById(\'payment-form\');
        form.addEventListener(\'submit\', function(ev) {

                ev.preventDefault();
                
                cleanFeedback();
                
                $("#alert-payment").slideUp("slow");
                $("#submit").attr("disabled", true);
                $("#submit").html(\' <i class="fa fa-spinner fa-spin fa-fw"></i>  processing... \');

                Xendit.setPublishableKey("'. env("XENDIT_PUBLIC_KEY") .'");
                
                var cardNumber = $("#card-number").val();
                var expiry = $("#cc-expiration").val();
                var expiryArray = expiry.split("/");
                var expiryMonth = expiryArray[0].trim();
                var expiryYear = expiryArray[1].trim();
                var cvvNumber = $("#cc-cvv").val();

                
                if(expiryYear.length==2)
                {
                    expiryYear = "'. substr(date('Y'),0,2) .'"+ expiryYear;
                }

                if(!payform.validateCardNumber(cardNumber))
                {
                    $("#card-number").addClass("is-invalid");
                    enableButton();
                    return false;
                }
            
                if(!payform.validateCardExpiry(expiryMonth,expiryYear))
                {
                    $("#cc-expiration").addClass("is-invalid");
                    enableButton();
                    return false;
                }

                if(!payform.validateCardCVC(cvvNumber))
                {
                    $("#cc-cvv").addClass("is-invalid");
                    enableButton();
                    return false;
                }


                $("#card-number").attr("disabled", true);
                $("#cc-expiration").attr("disabled", true);
                $("#cc-cvv").attr("disabled", true);

                cardNumber = cardNumber.replace(/\s/g,"");
                expiryMonth = expiryMonth.trim();
                expiryYear = expiryYear.trim();
                cvvNumber = cvvNumber.trim();
                
                Xendit.card.createToken({
                    amount: '.$amount.',
                    card_number: cardNumber,
                    card_exp_month: expiryMonth,
                    card_exp_year: expiryYear,
                    card_cvn: cvvNumber,
                    is_multiple_use: false
                }, xenditResponseHandler);


                return false;
            });
        

        ';
        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function stripe_jscript($sessionId)
    {
        
        
        $shoppingcart = Cache::get('_'. $sessionId);
        $amount = BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,'USD');
        $amount = $amount * 100;
        $jscript = '
        
            $("#submitCheckout").slideUp("slow");
            $("#paymentContainer").html(\'<form id="payment-form"><div id="stripe-wallet" class="pt-2 pb-2 justify-content-center"><h2>Pay with</h2><div id="payment-request-button"></div><div class="mt-2 mb-2" style="width: 100%; height: 12px; border-bottom: 1px solid #D0D0D0; text-align: center"><span style="color: #D0D0D0; font-size: 12px; background-color: #FFFFFF; padding: 0 10px;">or pay with card</span></div></div><div class="form-control mt-2 mb-2" style="height:47px;" id="card-element"></div><div id="card-errors" role="alert"></div><button style="height:47px;" class="btn btn-lg btn-block btn-theme" id="submit"><strong>Pay with card</strong></button></form><div id=\"loader\" class=\"mb-4\"></div><div id=\"text-alert\" class=\"text-center\"></div>\');

            

                 var stripe = Stripe(\''. env("STRIPE_PUBLISHABLE_KEY") .'\', {
                    apiVersion: "2020-08-27",
                 });

                 
                 var paymentRequest = stripe.paymentRequest({
                    country: \'US\',
                    currency: \'usd\',
                    total: {
                        label: \''. env('APP_NAME') .'\',
                        amount: '. $amount .',
                    },
                    requestPayerName: true,
                    requestPayerEmail: true,
                 });
                 

                 var elements = stripe.elements();

                 
                 var prButton = elements.create(\'paymentRequestButton\', {
                    paymentRequest: paymentRequest,
                 });

                 paymentRequest.canMakePayment().then(function(result) {
                    if (result) {
                        prButton.mount(\'#payment-request-button\');
                    } else {
                        document.getElementById(\'stripe-wallet\').style.display = \'none\';
                    }
                 });
                 

                 var style = {
                    base: {
                        color: "#32325d",
                        fontSize: "16px",
                        lineHeight: "34px"
                    }
                 };

                 var card = elements.create("card", { style: style });
                 card.mount("#card-element");

                var form = document.getElementById(\'payment-form\');
                form.addEventListener(\'submit\', function(ev) {
                   
                    ev.preventDefault();

                    
                    $("#alert-payment").slideUp("slow");
                    $("#submit").attr("disabled", true);
                    $("#submit").html(\' <i class="fa fa-spinner fa-spin fa-fw"></i>  processing... \');

                    $.ajax({
                    beforeSend: function(request) {
                        request.setRequestHeader(\'sessionId\', \''. $shoppingcart->session_id .'\');
                    },
                    type: \'POST\',
                    url: \''. env('APP_API_URL') .'/payment/stripe\'
                }).done(function( data ) {
                    
                    $("#loader").show();
                    $("#payment-form").slideUp("slow");  
                    $("#proses").hide();
                    $("#loader").addClass("loader");
                    $("#text-alert").show();
                    $("#text-alert").prepend( "Please wait and do not close the browser or refresh the page" );

                    stripe.confirmCardPayment(data.intent.client_secret, {
                        payment_method: {
                            card: card
                        }
                    }).then(function(result) {

                       

                        if (result.error) {

                            $("#text-alert").hide();
                            $("#text-alert").empty();
                            $("#loader").hide();
                            $("#loader").removeClass("loader");
                            $("#payment-form").slideDown("slow");
                            $("#submit").attr("disabled", false);
                            $("#submit").html(\'<strong>Pay with card</strong>\');
                            $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> \'+ result.error.message +\'</h2></div>\');
                            $(\'#alert-payment\').fadeIn("slow");

                        } else {
                            
                            if (result.paymentIntent.status === \'succeeded\' || result.paymentIntent.status === \'requires_capture\') {
                                
                                    

                                $.ajax({
                                data: {
                                    "authorizationID": result.paymentIntent.id,
                                    "sessionId": \''.$sessionId.'\',
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/stripe/confirm\'
                                }).done(function(data) {
                                if(data.id=="1")
                                {
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000); 
                                }

                                }).fail(function(error) {
                                    
                                });





                                
                            }
                        }
                    });
                });


                });



                paymentRequest.on(\'paymentmethod\', async(e) => {

                    const {intent} = await fetch("'. env('APP_API_URL') .'/payment/stripe", {
                        method: "POST",
                        credentials: \'same-origin\',
                        headers: {
                            "sessionId" : "'. $shoppingcart->session_id .'",
                        },
                    }).then(r => r.json());
                    
                    const {error,paymentIntent} = await stripe.confirmCardPayment(intent.client_secret,{
                            payment_method: e.paymentMethod.id
                        }, {handleActions:false});
                    
                    $("#payment-form").slideUp("slow");  
                    $("#proses").hide();
                    $("#loader").addClass("loader");
                    $("#text-alert").show();
                    $("#text-alert").prepend( "Please wait and do not close the browser or refresh the page" );

                    if(error) {
                        e.complete("fail");
                        $("#text-alert").hide();
                        $("#text-alert").empty();
                        $("#loader").hide();
                        $("#loader").removeClass("loader");
                        $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Payment Failed</h2></div>\');
                        $(\'#alert-payment\').fadeIn("slow");
                    }

                    e.complete("success");



                    if(paymentIntent.status == "requires_action")
                    {
                        stripe.confirmCardPayment(intent.client_secret).then(function(result){
                            if(result.error)
                            {
                                // failed
                                e.complete("fail");
                                $("#text-alert").hide();
                                $("#text-alert").empty();
                                $("#loader").hide();
                                $("#loader").removeClass("loader");
                                $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Payment Failed</h2></div>\');
                                $(\'#alert-payment\').fadeIn("slow");
                            }
                            else
                            {
                                // success
                                $.ajax({
                                data: {
                                    "authorizationID": paymentIntent.id,
                                    "sessionId": \''.$sessionId.'\',
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/stripe/confirm\'
                                }).done(function(data) {
                                if(data.id=="1")
                                {
                                    e.complete("success");
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000); 
                                }

                                }).fail(function(error) {
                                    
                                });
                            }
                        });
                        
                    } else {
                                // success
                                $.ajax({
                                data: {
                                    "authorizationID": paymentIntent.id,
                                    "sessionId": \''.$sessionId.'\',
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/stripe/confirm\'
                                }).done(function(data) {
                                if(data.id=="1")
                                {
                                    e.complete("success");
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000); 
                                }

                                }).fail(function(error) {
                                    
                                });
                    }

                });
            

        ';
        return response($jscript)->header('Content-Type', 'application/javascript');
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

    public function paypal_jscript($sessionId)
    {
        
        $jscript = '
        jQuery(document).ready(function($) {

            $("#submitCheckout").slideUp("slow");  
            $("#paymentContainer").html(\'<div id="proses"><h2 class="mt-0">Pay with</h2><div id="paypal-button-container"></div></div><div id=\"loader\" class=\"mb-4\"></div><div id=\"text-alert\" class=\"text-center\"></div>\');
           
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
                    $("#text-alert").prepend( "Please wait and do not close the browser or refresh the page" );

                    actions.order.capture().then(function(orderData) {
                            
                            $.ajax({
                                data: {
                                    "orderID": data.orderID,
                                    "sessionId": \''.$sessionId.'\',
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/paypal/confirm\'
                            }).done(function(data) {
                                if(data.id=="1")
                                {
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000);
                                }
                                else
                                {
                                    $("#loader").hide();
                                    $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Payment Failed!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                }
                            }).fail(function(error) {
                                
                            });

                    });
                }
            }).render(\'#paypal-button-container\');
        });';
        
        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    

    
    
}
