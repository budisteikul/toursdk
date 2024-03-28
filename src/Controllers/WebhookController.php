<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\PaymentHelper;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Helpers\WiseHelper;
use budisteikul\toursdk\Helpers\TaskHelper;

class WebhookController extends Controller
{
    
	
    public function __construct()
    {
        
    }
    
    public function webhook($webhook_app,Request $request)
    {
        if($webhook_app=="wise")
        {
            $is_test = $request->header('X-Test-Notification');
            if($is_test)
            {
                return response('OK', 200)->header('Content-Type', 'text/plain');
            }

            $signature = $request->header('X-Signature-SHA256');
            $delivery_id = $request->header('X-Delivery-Id');
            $json      = $request->getContent();
            $tw = new WiseHelper();
            $verify = $tw->checkSignature($json,$signature);

            if($verify)
            {
                $data = json_decode($json);
                $amount = $data->data->amount;
                $currency = $data->data->currency;
                $profileId = $data->data->resource->profile_id;
                $customerTransactionId = $delivery_id;


		        $payload = new \stdClass();
                $payload->amount = $amount;
                $payload->currency = $currency;
                $payload->app = 'wise';
                $payload->customerTransactionId = $customerTransactionId;
                $payload->profileId = $profileId;
                $payload->token = env('WISE_TOKEN');

		        TaskHelper::create($payload);
		        
		        return response('OK', 200)->header('Content-Type', 'text/plain');
            }
            

            return response('ERROR', 200)->header('Content-Type', 'text/plain');
        }


        if($webhook_app=="bokun")
        {
            

            $data = json_decode($request->getContent(), true);

            $bookingChannel = '';
            if(isset($data['affiliate']['title']))
            {
                $bookingChannel = $data['affiliate']['title'];
            }
            else
            {
                $bookingChannel = $data['seller']['title'];
            }

            $confirmation_code = '';
            if(isset($data['externalBookingReference']))
            {
                $confirmation_code = $data['externalBookingReference'];
            }
            else
            {
                $confirmation_code = $data['confirmationCode'];
            }

            if($bookingChannel=="Viator.com") $confirmation_code = 'BR-'. $data['externalBookingReference'];
            

            $status = $data['status'];

            switch($status)
            {
                case 'CONFIRMED':
                    
                    $count = Shoppingcart::where('confirmation_code',$confirmation_code)->where('booking_status','CONFIRMED')->count();

                    if($count>0)
                    {
                        $shoppingcart = Shoppingcart::where('confirmation_code',$confirmation_code)->where('booking_status','CONFIRMED')->first();
                        PaymentHelper::confirm_payment($shoppingcart,"CANCELED",true);
                        BookingHelper::shoppingcart_notif($shoppingcart);
                    }

                    $shoppingcart = BookingHelper::webhook_bokun($data);
                    PaymentHelper::confirm_payment($shoppingcart,"CONFIRMED",true);
                    BookingHelper::shoppingcart_whatsapp($shoppingcart);
                    BookingHelper::shoppingcart_notif($shoppingcart);
                    
                    return response('CONFIRMED OK', 200)->header('Content-Type', 'text/plain');
                break;
                case 'CANCELLED':
                    if(Shoppingcart::where('confirmation_code',$confirmation_code)->where('booking_status','CONFIRMED')->count()>0)
                    {
                        $shoppingcart = Shoppingcart::where('confirmation_code',$confirmation_code)->where('booking_status','CONFIRMED')->first();
                        PaymentHelper::confirm_payment($shoppingcart,"CANCELED",true);
                        BookingHelper::shoppingcart_notif($shoppingcart);
                    }
                    return response('CANCELLED OK', 200)->header('Content-Type', 'text/plain');
                break;
            }
        }

        return response('ERROR', 200)->header('Content-Type', 'text/plain');
    }

}
