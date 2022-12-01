<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Models\Shoppingcart;

use budisteikul\toursdk\Helpers\WiseHelper;
use budisteikul\toursdk\Helpers\LogHelper;
use budisteikul\toursdk\Helpers\TaskHelper;

use budisteikul\toursdk\Helpers\ProductHelper;
use budisteikul\toursdk\Helpers\ContentHelper;

class WebhookController extends Controller
{
    
	
    public function __construct()
    {
        
    }

    

    public function test(Request $request)
    {
        
    }
	
    

    public function webhook($webhook_app,Request $request)
    {
        if($webhook_app=="wise")
        {
            LogHelper::log_webhook($request->getContent());

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
                $customerTransactionId = $delivery_id;


		        $payload = new \stdClass();
                $payload->amount = $amount;
                $payload->currency = $currency;
                $payload->app = 'wise';
                $payload->customerTransactionId = $customerTransactionId;
                $payload->token = env('WISE_TOKEN');

		        TaskHelper::create($payload);
		        
		        return response('OK', 200)->header('Content-Type', 'text/plain');
            }
            

            return response('ERROR', 200)->header('Content-Type', 'text/plain');
        }


        if($webhook_app=="bokun")
        {
            LogHelper::log_webhook($request->getContent());

            $data = json_decode($request->getContent(), true);
            
            switch($request->input('action'))
            {
            case 'BOOKING_CONFIRMED':

                if(Shoppingcart::where('confirmation_code','BR-'.$data['externalBookingReference'])->count()==0)
                {
                    $shoppingcart = BookingHelper::webhook_insert_shoppingcart($data);
                    BookingHelper::confirm_payment($shoppingcart,"CONFIRMED",true);
                    BookingHelper::shoppingcart_mail($shoppingcart);
                    BookingHelper::shoppingcart_notif($shoppingcart);
                }
                return response('OK', 200)->header('Content-Type', 'text/plain');
            break;
            case 'BOOKING_ITEM_CANCELLED':
                $shoppingcart = Shoppingcart::where('confirmation_code','BR-'.$data['externalBookingReference'])->firstOrFail();
                
                BookingHelper::confirm_payment($shoppingcart,"CANCELED",true);
                

                return response('OK', 200)->header('Content-Type', 'text/plain');
            break;
            }
        }

        return response('ERROR', 200)->header('Content-Type', 'text/plain');
    }

}
