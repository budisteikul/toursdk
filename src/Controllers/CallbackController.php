<?php
namespace budisteikul\toursdk\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

use budisteikul\toursdk\Models\ShoppingcartPayment;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\PaymentHelper;
use budisteikul\toursdk\Helpers\FirebaseHelper;
use budisteikul\toursdk\Helpers\PaypalHelper;
use budisteikul\toursdk\Helpers\VoucherHelper;

class CallbackController extends Controller
{
    
    public function __construct()
    {
        
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
                PaymentHelper::confirm_payment($shoppingcart_payment->shoppingcart,"CONFIRMED");
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
                PaymentHelper::create_payment($sessionId,"xendit","ovo");
                $shoppingcart = BookingHelper::confirm_booking($sessionId);
                FirebaseHelper::upload_payment('CONFIRMED',$reference_id,$sessionId,'ovo',"/booking/receipt/".$shoppingcart->session_id."/".$shoppingcart->confirmation_code);
                BookingHelper::shoppingcart_notif($shoppingcart_payment->shoppingcart);
            }

            if($channel_code=="ID_DANA")
            {
                $shoppingcart_payment = ShoppingcartPayment::where('payment_provider','xendit')->where('order_id',$reference_id)->first();
                if($shoppingcart_payment){
                    PaymentHelper::confirm_payment($shoppingcart_payment->shoppingcart,"CONFIRMED");
                    BookingHelper::shoppingcart_notif($shoppingcart_payment->shoppingcart);
                }
            }
        }

        if($event=="qr.payment")
        {
                $shoppingcart_payment = ShoppingcartPayment::where('payment_provider','xendit')->where('order_id',$reference_id)->first();
                if($shoppingcart_payment){
                    PaymentHelper::confirm_payment($shoppingcart_payment->shoppingcart,"CONFIRMED");
                    BookingHelper::shoppingcart_notif($shoppingcart_payment->shoppingcart);
                    $shoppingcart_payment->authorization_id = $data['data']['id'];
                    $shoppingcart_payment->save();
                }
        }
        

        return response()->json([
                'message' => "success"
            ], 200);
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

}
