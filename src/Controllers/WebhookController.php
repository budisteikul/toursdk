<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Models\Shoppingcart;

use budisteikul\toursdk\Helpers\WiseHelper;
use budisteikul\toursdk\Helpers\LogHelper;

use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;

class WebhookController extends Controller
{
    public function test2(Request $request)
    {
 	$tw = new WiseHelper();
        $quote = $tw->postCreateQuote(10,'USD');
        $transfer = $tw->postCreateTransfer($quote->id);
        $fund = $tw->postFundTransfer($transfer->id);
	
    }
	
    public function __construct()
    {
        
    }

    

    public function test(Request $request)
    {
        $tw = new WiseHelper();
        $aaa = $tw->simulateAddFund();
        print_r($aaa);
	
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
		
		$projectId = 'igneous-thunder-361818';
$locationId = 'us-central1';
$queueId = 'vertikaltrip';
$payload = null;
$url = env('APP_TASK_URL') .'/test2';
$client = new CloudTasksClient();
$queueName = $client->queueName($projectId, $locationId, $queueId);

$httpRequest = new HttpRequest();
$httpRequest->setUrl($url);
$httpRequest->setHttpMethod(HttpMethod::POST);
if (isset($payload)) {
    $httpRequest->setBody($payload);
}

$task = new Task();
$task->setHttpRequest($httpRequest);

$response = $client->createTask($queueName, $task);
		//sleep(5);
		//$tw = new WiseHelper();
                //$quote = $tw->postCreateQuote($amount,$currency);
                //$transfer = $tw->postCreateTransfer($quote->id);
                //$fund = $tw->postFundTransfer($transfer->id);
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
