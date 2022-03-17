<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Helpers\ProductHelper;
use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\ImageHelper;
use budisteikul\toursdk\Helpers\GeneralHelper;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\ContentHelper;
use budisteikul\toursdk\Helpers\FirebaseHelper;
use budisteikul\toursdk\Helpers\PaypalHelper;

use budisteikul\toursdk\Models\Disbursement;
use budisteikul\toursdk\Models\Category;
use budisteikul\toursdk\Models\Review;
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
use Barryvdh\DomPDF\Facade as PDF;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class APIController extends Controller
{
    
    public function test()
    {
        print_r(storage_path());
    }

    public function __construct()
    {
        $this->currency = env("BOKUN_CURRENCY");
        $this->lang = env("BOKUN_LANG");
        
        $this->midtransServerKey = env("MIDTRANS_SERVER_KEY");
        $this->oyApiKey = env("OY_API_KEY");
        $this->dokuSecretKey = env("DOKU_SECRET_KEY");

        $this->appUrl = env("APP_URL");
    }

    public function redirect($sessionId,$confirmationCode)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$confirmationCode)->where('session_id', $sessionId)->firstOrFail();
        return redirect($shoppingcart->shoppingcart_payment->redirect);
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

    public function downloadQrcode($sessionId,$id)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$id)->where('session_id',$sessionId)->firstOrFail();
        $qrcode = BookingHelper::generate_qris($shoppingcart);
        $path = Storage::disk('local')->put($shoppingcart->confirmation_code .'.png', $qrcode);
        return response()->download(storage_path('app').'/'.$shoppingcart->confirmation_code .'.png')->deleteFileAfterSend(true);
        //$payments = $shoppingcart->shoppingcart_payment()->first();
        //$contents = file_get_contents($payments->qrcode);
        //$path = Storage::disk('local')->put($shoppingcart->confirmation_code .'.png', $contents);
        //return response()->download(storage_path('app').'/'.$shoppingcart->confirmation_code .'.png')->deleteFileAfterSend(true);
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

    public function navbar($sessionId)
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

    public function review_count()
    {
        $count = Review::count();
        return response()->json([
            'message' => 'success',
            'count' => $count
        ], 200);
    }
 
    public function categories()
    {

        $dataObj = ContentHelper::view_categories();

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

        if(!isset($shoppingcart->products))
        {
            abort(404);
        }

        $dataShoppingcart = ContentHelper::view_shoppingcart($shoppingcart);

        return response()->json([
            'message' => 'success',
            'shoppingcarts' => $dataShoppingcart,
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

    public function last_order($sessionId)
    {
        $shoppingcarts = Shoppingcart::where('session_id', $sessionId)->orderBy('id','desc')->get();
        
        if($shoppingcarts->isEmpty())
        {
            abort(404);
        }
        
        $booking = ContentHelper::view_last_order($shoppingcarts);
        
        $data = array(
                'receipt' => $booking,
                'message' => 'success'
            );

       

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

        $data = array(
                'receipt' => $dataObj,
                'message' => 'success'
            );

        FirebaseHelper::connect('receipt/'.$shoppingcart->session_id ."/". $shoppingcart->confirmation_code,$data,"PUT");
        
        return response()->json([
                'receipt' => $dataObj,
                'message' => "success"
            ], 200);
    }
    
    public function confirmdisbursementoy(Request $request)
    {
        
            $data = $request->all();
            $transaction_id = $data['partner_trx_id'];
            $status = $data['status']['code'];

            $disbursement = Disbursement::where('transaction_id',$transaction_id)->first();
            if($disbursement!==null)
            {
                if($status=="000")
                {
                    $disbursement->status = 2;
                    $disbursement->save();
                }
            }
       
            return response('OK', 200)->header('Content-Type', 'text/plain');
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
                    "message" => "/booking/receipt/".$shoppingcart->session_id."/".$shoppingcart->confirmation_code
                ]);
        
    }

    public function confirmpaymentoy(Request $request)
    {
            $data = $request->all();

            $order_id = null;
            $status = null;
            if(isset($data['partner_tx_id'])) $order_id = $data['partner_tx_id'];
            if(isset($data['partner_trx_id'])) $order_id = $data['partner_trx_id'];
            if(isset($data['status'])) $status = strtolower($data['status']);
            if(isset($data['settlement_status'])) $status = strtolower($data['settlement_status']);
            
            $shoppingcart_payment = ShoppingcartPayment::where('order_id',$order_id)->first();
            if($shoppingcart_payment!==null){
                $confirmation_code = $shoppingcart_payment->shoppingcart->confirmation_code;
                $shoppingcart = Shoppingcart::where('confirmation_code',$confirmation_code)->first();
                if($shoppingcart!==null)
                {
                    if($status=="complete")
                    {
                        BookingHelper::confirm_payment($shoppingcart,"CONFIRMED");
                    }
                    if($status=="success")
                    {
                        BookingHelper::confirm_payment($shoppingcart,"CONFIRMED");
                    }
                    if($status=="expired")
                    {
                        BookingHelper::confirm_payment($shoppingcart,"CANCELED");
                    }
                    if($status=="failed")
                    {
                        BookingHelper::confirm_payment($shoppingcart,"CANCELED");
                    }
                }
            }
                
        
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    public function confirmpaymentdoku(Request $request)
    {
        
            $data = $request->all();

            $order_id = null;
            $transaction_status = null;

            if(isset($data['order']['invoice_number'])) $order_id = $data['order']['invoice_number'];
            if(isset($data['transaction']['status'])) $transaction_status = $data['transaction']['status'];
            if(isset($data['TRANSACTIONID'])) $order_id = $data['TRANSACTIONID'];
            if(isset($data['TXNSTATUS'])) $transaction_status = $data['TXNSTATUS'];

            $shoppingcart_payment = ShoppingcartPayment::where('order_id',$order_id)->first();
            if($shoppingcart_payment!==null) {
                $confirmation_code = $shoppingcart_payment->shoppingcart->confirmation_code;
                $shoppingcart = Shoppingcart::where('confirmation_code',$confirmation_code)->first();
                if($shoppingcart!==null)
                {
                    if($transaction_status=="SUCCESS")
                    {
                        BookingHelper::confirm_payment($shoppingcart,"CONFIRMED");
                    }
                    else if($transaction_status=="S")
                    {
                        BookingHelper::confirm_payment($shoppingcart,"CONFIRMED");
                    }
                    else if ($transaction_status=="PENDING")
                    {
                        BookingHelper::confirm_payment($shoppingcart,"PENDING");      
                    }
                    else
                    {
                        BookingHelper::confirm_payment($shoppingcart,"CANCELED");
                    }
                }
            }
                
        
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    public function confirmpaymentmidtrans(Request $request)
    {
        
            $data = $request->all();



            $order_id = null;
            if(isset($data['order_id'])) $order_id = $data['order_id'];

            $shoppingcart_payment = ShoppingcartPayment::where('order_id',$order_id)->first();
            if($shoppingcart_payment!==null) {
                $confirmation_code = $shoppingcart_payment->shoppingcart->confirmation_code;
                $shoppingcart = Shoppingcart::where('confirmation_code',$confirmation_code)->first();
                if($shoppingcart!==null)
                {
                    if(hash('sha512', $order_id.$data['status_code'].$data['gross_amount'].$this->midtransServerKey)==$data['signature_key'])
                    {
                        if($data['transaction_status']=="settlement")
                        {
                            BookingHelper::confirm_payment($shoppingcart,"CONFIRMED");
                        }
                        else if($data['transaction_status']=="pending")
                        {
                            BookingHelper::confirm_payment($shoppingcart,"PENDING");
                        }
                        else
                        {
                            BookingHelper::confirm_payment($shoppingcart,"CANCELED");
                        }
                    
                    }
                }
            }
                
            return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    public function createpaymentpaypal(Request $request)
    {
        $sessionId = $request->header('sessionId');
        BookingHelper::set_confirmationCode($sessionId);
        $response = BookingHelper::create_payment($sessionId,"paypal");
        return response()->json($response);
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
            
            $payment = $data['payment'];

            if($payment=="paypal") {

                return response()->json([
                    'message' => 'success',
                ], 200);

            }
            else if($payment=="qris")
            {
                BookingHelper::set_bookingStatus($sessionId,'PENDING');
                BookingHelper::set_confirmationCode($sessionId);
                BookingHelper::create_payment($sessionId,"doku","qris");
            }
            else
            {
                $payment_arr = explode("-",$payment);

                $payment_provider = NULL;
                $payment_bank = NULL;

                if(isset($payment_arr[0])) $payment_provider = $payment_arr[0];
                if(isset($payment_arr[1])) $payment_bank = $payment_arr[1];

                BookingHelper::set_bookingStatus($sessionId,'PENDING');
                BookingHelper::set_confirmationCode($sessionId);
                BookingHelper::create_payment($sessionId,$payment_provider,$payment_bank);
            }
            
            $shoppingcart = BookingHelper::confirm_booking($sessionId);

            $text = null;
            $redirect_type = 1;
            $redirect = $shoppingcart->shoppingcart_payment->redirect;
            if(substr($redirect,0,1)!="/")
            {
                $redirect_type = 2;
                if($shoppingcart->shoppingcart_payment->bank_name=="gopay")
                {
                    $text = '<img src="'. url('/img/ewallet/gopay-light.png') .'" height="30" />';
                }
                if($shoppingcart->shoppingcart_payment->bank_name=="shopeepay")
                {
                    $text = '<img src="'. url('/img/ewallet/shopeepay-light.png') .'" height="30" />';
                }
                if($shoppingcart->shoppingcart_payment->bank_name=="linkaja")
                {
                    $text = '<img src="'. url('/img/ewallet/linkaja-light.png') .'" height="30" />';
                }
                $text = '<strong>Pay with '. $text .'</strong>';
            }

            return response()->json([
                "message" => "success",
                "id" => $redirect_type,
                "redirect" => $redirect,
                "text" => $text
            ]);
            
    }

    public function paypal_jscript($sessionId)
    {
        
        $jscript = '
        jQuery(document).ready(function($) {

            $("#submitCheckout").slideUp("slow");  
            $("#paymentContainer").html(\'<div id="proses"><h2>Pay with</h2><div id="paypal-button-container"></div></div><div id=\"loader\" class=\"mb-4\"></div><div id=\"text-alert\" class=\"text-center\"></div>\');
           
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

    
    
}
