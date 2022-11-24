<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Models\Shoppingcart;

use budisteikul\toursdk\Helpers\WiseHelper;
use budisteikul\toursdk\Helpers\LogHelper;
use budisteikul\toursdk\Helpers\TaskHelper;
use Ramsey\Uuid\Uuid;

use budisteikul\toursdk\Helpers\RapydHelper;

class WebhookController extends Controller
{
    
	
    public function __construct()
    {
        
    }

    

    public function test(Request $request)
    {
        
        $body = [
                'amount' => '15000',
                'currency' => 'KRW',
                'description' => 'VERTIKAL TRIP',
                'complete_payment_url' => 'https://4c97-125-160-109-212.ap.ngrok.io/',
                'error_payment_url' => 'https://4c97-125-160-109-212.ap.ngrok.io/',
                'payment_method' => [
                    'type' => 'kr_tmoney_ewallet',
                    'fields' => []
                ],
                
            ];

        $data1 = RapydHelper::make_request('post','/v1/payments',$body);
        print_r($data1);

        //$object = RapydHelper::make_request('get', '/v1/payment_methods/country?country=KR&currency=KRW');
        //print_r($object);

        $body = [
            'currency' => 'SGD',
            'country' => 'SG',
            'description' => 'Issuing bank account number to wallet',
            'ewallet' => 'ewallet_59e4416234ce43408e9816f41a10f744'
        ];

        //$object = RapydHelper::make_request('post', '/v1/issuing/bankaccounts', $body);
        //var_dump($object);

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
            $json      = $request->getContent();
            $tw = new WiseHelper();
            $verify = $tw->checkSignature($json,$signature);

            if($verify)
            {
                $data = json_decode($json);
                $amount = $data->data->amount;
                $currency = $data->data->currency;
		        $sent_at = $data->sent_at;
                $customerTransactionId = Uuid::uuid5(Uuid::NAMESPACE_URL, $sent_at);


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
