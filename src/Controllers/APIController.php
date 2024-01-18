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
use budisteikul\toursdk\Helpers\ReviewHelper;

use budisteikul\toursdk\Models\Category;
use budisteikul\toursdk\Models\Review;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Channel;
use budisteikul\toursdk\Models\Page;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartProduct;
use budisteikul\toursdk\Models\ShoppingcartCancellation;

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
        
    }

    public function cancellation($sessionId,$confirmationCode)
    {
        $shoppingcart = Shoppingcart::where('session_id',$sessionId)->where('confirmation_code',$confirmationCode)->first();
        if($shoppingcart)
        {
            $check = ShoppingcartCancellation::where('shoppingcart_id', $shoppingcart->id)->first();
            if(!$check)
            {
                $shoppingcart_cancellation = new ShoppingcartCancellation();
                $shoppingcart_cancellation->status = 1;
                $shoppingcart_cancellation->shoppingcart_id = $shoppingcart->id;
                $shoppingcart_cancellation->amount = 100;
                $shoppingcart_cancellation->save();
            }
        }
    }

    public function config(Request $request)
    {
        $paypal_sdk = 'https://www.paypal.com/sdk/js?client-id='.env("PAYPAL_CLIENT_ID").'&currency='. env("PAYPAL_CURRENCY").'&disable-funding=credit,card';
        
        $payment_enable = config('site.payment_enable');
        $payment_array = explode(",",$payment_enable);
        
        if(in_array('xendit',$payment_array)) {
            $jscripts[] = ['https://js.xendit.co/v1/xendit.min.js',false];
            $jscripts[] = [ config('site.assets') .'/js/payform.min.js',true];
        }
        if(in_array('stripe',$payment_array)) $jscripts[] = ['https://js.stripe.com/v3/', true];
        if(in_array('paypal',$payment_array)) $jscripts[] = [$paypal_sdk, true];
        
        $analytic = LogHelper::analytic();

        $headerBox = '
        <img src="'.config('site.assets').'/img/header/jogjafoodtour.png" alt="Jogja Food Tour" width="250" />
        <hr class="hr-theme" />
        <p class="text-faded">
          Join us on this experience to try authentic Javanese dishes, play traditional games, travel on a becak, learn interesting fun facts about city, interact with locals and many more.
          <br />
          Enjoy Jogja in Local Ways!
        </p>';

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

        $dataPrivacyTerm[] = [
            'title' => 'Terms and Conditions',
            'link' => '/page/terms-and-conditions'
        ];

        $dataPrivacyTerm[] = [
            'title' => 'Privacy Policy',
            'link' => '/page/privacy-policy'
        ];

        $company = config('site.company');
        $footerTitle = config('site.footer');

        $tourGuides[] = [
            'image' => config('site.assets').'/img/guide/kalika-ratna.jpg',
            'name' => 'Kalika',
            'description' => 'Tour Guide in Jogja',
        ];

        $tourGuides[] = [
            'image' => config('site.assets').'/img/guide/anisa.jpg',
            'name' => 'Anisa',
            'description' => 'Tour Guide in Jogja',
        ];

        $tourGuides[] = [
            'image' => config('site.assets').'/img/guide/budi.jpg',
            'name' => 'Budi',
            'description' => 'Partner Relation',
        ];

        $services[] = [
            'icon' => '<i class="fa fa-4x fa-bolt text-theme mb-2"></i>',
            'name' => 'Instant Confirmation',
            'description' => 'To secure your spot while keeping your plans flexible. Your booking are confirmed automatically!',
        ];

        $services[] = [
            'icon' => '<i class="fas fa-4x fa-phone-alt text-theme mb-2"></i>',
            'name' => '24/7 Support',
            'description' => 'Stay Connected with us! With 24/7 Support.',
        ];

        $services[] = [
            'icon' => '<i class="fas fa-4x fa-history text-theme mb-2"></i>',
            'name' => 'Full Refund',
            'description' => 'Have your plans changed? No worries! You can cancel the booking anytime!',
        ];

        $services[] = [
            'icon' => '<i class="fa fa-4x fa-utensils text-theme mb-2"></i>',
            'name' => 'Customizable',
            'description' => 'We can accommodate many food restrictions — Just fill it on booking form!',
        ];

        return response()->json([
            'jscripts' => $jscripts,
            'analytic' => $analytic,
            'assets' => config('site.assets'),
            'featured' => '

                <div class="row pb-0">
                    <div class="col-lg-8 text-center mx-auto">
                        <h3 class="section-heading" style="margin-top:50px;">Yogyakarta: The way to this city’s heart is through its food</h3>
                        <div class="col-lg-8 text-center mx-auto">
                            Perhaps better known for being a bastion of history and culture, Yogyakarta is also the unofficial culinary capital of Indonesia
                        </div>
                        <br />
                        <hr class="hr-theme" />
                    </div>
                </div>

                 <div class="row text-center">
                    <div class="col-md-8 mx-auto">
                        <img src="'.config('site.assets').'/img/content/silkwinds.jpg" alt="Silkwinds | Jogja Food Tour" class="img-fluid rounded" />
                        <img src="'.config('site.assets').'/img/content/silkwinds-magazine-logo.png" alt="Silkwinds | Jogja Food Tour" style={{ marginTop: "4px" }} class="img-fluid rounded" />
                        <span class="caption text-muted"><a class="text-muted" rel="noreferrer" target="_blank" href="https://www.silverkris.com/yogyakarta-the-way-to-this-citys-heart-is-through-its-food/">Silkwinds Magazine</a></span>
                    </div>
                </div>

            ',
            'tourGuides' => $tourGuides,
            'services' => $services,
            'headerBox' => $headerBox,
            'headerBackground' => config('site.assets').'/img/header/background.jpg',
            'footerUsefullLinks' => $usefullLink,
            'footerPrivacyterms' => $dataPrivacyTerm,
            'footerWhatsapp' => '+6285743112112',
            'footerCompany' => $company,
            'footerTitle' => $footerTitle,
            'footerPartners' => [
                '<a target="_blank" rel="noreferrer noopener" href="https://www.getyourguide.com/yogyakarta-l349/yogyakarta-night-walking-and-food-tour-t429708"><img height="30" class="mb-1 mt-2 mr-2 img-thumbnail" src="'.config('site.assets').'/img/footer/getyourguide-logo.png"} alt="GetYourGuide" /></a>',
                '<a target="_blank" rel="noreferrer noopener" href="https://www.airbnb.com/experiences/434368"><img height="30" class="mb-1 mt-2 mr-2 img-thumbnail" src="'.config('site.assets').'/img/footer/airbnb-logo.png"} alt="Airbnb" /></a>',
                '<a target="_blank" rel="noreferrer noopener" href="https://www.tripadvisor.com/AttractionProductReview-g14782503-d15646790-Small_Group_Walking_and_Food_Tour_by_Night_in_Yogyakarta-Yogyakarta_Yogyakarta_R.html"><img height="30" class="mb-1 mt-2 mr-2 img-thumbnail" src="'.config('site.assets').'/img/footer/tripadvisor-logo.png"} alt="Tripadvisor" /></a>',
                
                
            ],
            'footerPaymentChannels' => [
                '<img height="30" class="mt-2" src="'.config('site.assets').'/img/footer/line-1.png" alt="Payment Channels" /><br />',
                '<img height="30" class="mt-2" src="'.config('site.assets').'/img/footer/line-4.png" alt="Payment Channels" /><br />',
            ]
        ], 200);
    }

    public function navbar($sessionId)
    {
        
        if(str_contains(GeneralHelper::url(), 'jogjafoodtour'))
        {
            $categories = Category::where('parent_id',0)->where('slug','jogja-food-tour')->get();
            $logo = config('site.assets').'/img/header/'.config('site.logo');
        }
        else
        {
            $categories = Category::where('parent_id',0)->get();
            $logo = config('site.assets').'/img/header/vertikaltrip.svg';
        }
        
        $json_ld = self::json_ld();
        return response()->json([
            'message' => 'success',
            'logo' => $logo,
            'json_ld' => $json_ld,
            'categories' => $categories,
            'url' => GeneralHelper::url(),
            'title' => config('site.title'),
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

        $company = config('site.company');
        $footerTitle = config('site.footer');

        return response()->json([
            'message' => 'success',
            'usefullLinks' => $usefullLink,
            'whatsapp' => '+6285743112112',
            'privacyterms' => $dataPrivacyTerm,
            'company' => $company,
            'footerTitle' => $footerTitle,
            'partners' => [
                '<a target="_blank" rel="noreferrer noopener" href="https://www.getyourguide.com/yogyakarta-l349/yogyakarta-night-walking-and-food-tour-t429708"><img height="30" class="mb-1 mt-2 mr-2 img-thumbnail" src="'.config('site.assets').'/img/footer/getyourguide-logo.png"} alt="GetYourGuide" /></a>',
                '<a target="_blank" rel="noreferrer noopener" href="https://www.airbnb.com/experiences/434368"><img height="30" class="mb-1 mt-2 mr-2 img-thumbnail" src="'.config('site.assets').'/img/footer/airbnb-logo.png"} alt="Airbnb" /></a>',
                '<a target="_blank" rel="noreferrer noopener" href="https://www.tripadvisor.com/AttractionProductReview-g14782503-d15646790-Small_Group_Walking_and_Food_Tour_by_Night_in_Yogyakarta-Yogyakarta_Yogyakarta_R.html"><img height="30" class="mb-1 mt-2 mr-2 img-thumbnail" src="'.config('site.assets').'/img/footer/tripadvisor-logo.png"} alt="Tripadvisor" /></a>',
                
                
            ],
            'paymentChannels' => [
                '<img height="30" class="mt-2" src="'.config('site.assets').'/img/footer/line-1.png" alt="Payment Channels" /><br />',
                '<img height="30" class="mt-2" src="'.config('site.assets').'/img/footer/line-4.png" alt="Payment Channels" /><br />',
            ]
        ], 200);
    }

    public function review_count()
    {
        $count = ReviewHelper::review_count();
        $rate = ReviewHelper::review_rate();
        return response()->json([
            'message' => 'success',
            'count' => $count,
            'rate' => '('. $rate .')'
        ], 200);
    }

    public function json_ld()
    {
        $rating = ReviewHelper::review_rate();
        $count = ReviewHelper::review_count();
        $json = '
        {
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": "Yogyakarta Night Walking and Food Tours",
            "image": [
                "'.config('site.assets').'/img/schema/jogja-food-tour-1x1.jpg",
                "'.config('site.assets').'/img/schema/jogja-food-tour-4x3.jpg",
                "'.config('site.assets').'/img/schema/jogja-food-tour-16x9.jpg"
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
                "url": "'.GeneralHelper::url().'/tour/yogyakarta-night-walking-and-food-tours",
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
            $.fn.dataTableExt.sErrMode = \'throw\';
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

    public function product_add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        Cache::forget('_bokunProductById_'. config('site.currency') .'_'. env("BOKUN_LANG") .'_'.$data);
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

    public function categories()
    {
        if(str_contains(GeneralHelper::url(), 'jogjafoodtour'))
        {
            $category = Category::where('slug','jogja-food-tour')->firstOrFail();
            $dataObj = ContentHelper::view_category($category);
        }
        else
        {
            $dataObj = ContentHelper::view_categories();
        }

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

        FirebaseHelper::shoppingcart($sessionId);

        //$shoppingcart = Cache::get('_'. $sessionId);
        //$dataShoppingcart = ContentHelper::view_shoppingcart($shoppingcart);
        
        return response()->json([
            'message' => 'success',
            //'shoppingcarts' => $dataShoppingcart,
        ], 200);
    }
    
    public function product_remove(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        Cache::forget('_bokunProductById_'. config('site.currency') .'_'. env("BOKUN_LANG") .'_'.$data);

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

    public function snippetscalendar($activityId,$year,$month)
    {
        $contents = BookingHelper::get_calendar($activityId,$year,$month);
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
            
            $microtime = $availability[0]['date'];
            $month = date("n",$microtime/1000);
            $year = date("Y",$microtime/1000);

            if($embedded=="") $embedded = "true";

            $jscript = ' 
            
            var WidgetUtils = this.WidgetUtils = {};

    WidgetUtils.PriceFormatter = function(attributes) {
        $.extend(this, {
            currency: \''. config('site.currency') .'\',
            language: \''. env("BOKUN_LANG") .'\',
            decimalSeparator: \'.\',
            groupingSeparator: \',\',
            symbol: \''. config('site.currency') .' \'
        }, attributes);

        var instance = this;

        this.setCurrency = function(currency, symbol) {
            this.currency = currency;
            this.symbol = symbol;
        };

        this.format = function(amt) {
            if ( amt != null ) {
                return amt.toString().replace(/\B(?=(\d{3})+(?!\d))/g, instance.groupingSeparator);
            } else {
                return \'-\';
            }
        };

        this.symbolAndFormat = function(amt) {
            return (instance.symbol.length > 1 ? instance.symbol + " " : instance.symbol) + instance.format(amt);
        };

        this.formatHtml = function(amt) {
            return \'<span class="price"><span class="symbol">\' + (instance.symbol.length > 1 ? instance.symbol + " " : instance.symbol) + \'</span><span class="amount">\' + instance.format(amt) + \'</span></span>\';
        };

        this.formatHtmlSimple = function(amt) {
            return \'<span class="symbol">\' + (instance.symbol.length > 1 ? instance.symbol + " " : instance.symbol) + \'</span><span class="amount">\' + instance.format(amt) + \'</span>\';
        };

        this.formatHtmlStrikeThrough = function(amt) {
            return \'<span style="font-size: 14px">\' + (instance.symbol.length > 1 ? instance.symbol + " " : instance.symbol) + \'</span><span style="font-size: 14px">\' + instance.format(amt) + \'</span>\';
        }

    };

            window.priceFormatter = new WidgetUtils.PriceFormatter({
                currency: \''. config('site.currency') .'\',
                language: \''. env("BOKUN_LANG") .'\',
                decimalSeparator: \'.\',
                groupingSeparator: \',\',
                symbol: \''. config('site.currency') .' \'
            });

            window.i18nLang = \''. env("BOKUN_LANG") .'\';

            try { 
                $("#titleProduct").append(\''. $product->name .'\');
            } catch(err) {  
            }

            try { 
                $("#titleBooking").html(\'Booking '. $product->name .'\');
            } catch(err) {  
            }

            window.ActivityBookingWidgetConfig = {
                currency: \''. config('site.currency') .'\',
                language: \''. env("BOKUN_LANG") .'\',
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
                    $("#submit").html(\' <strong>Click to pay with <img class="ml-2 mr-2" src="'.config('site.assets').'/img/payment/ovo-light.png" height="30" /></strong> \');
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
