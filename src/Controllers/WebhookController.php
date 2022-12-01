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
        $confirmation_code = 'VT-221201001';
        $session_id = 'f0e0038-b5f-4fdb-75d6-aa24ec5012a';

        $shoppingcart = Shoppingcart::where('session_id',$session_id)->where('confirmation_code',$confirmation_code)->first();
        if($shoppingcart)
        {
                $message = '
';
                foreach($shoppingcart->shoppingcart_products as $product)
                {
                    $title = "New Booking: ". ProductHelper::datetotext($product->date) .' ('.$confirmation_code.')';
                    $message .= $product->title .'
';
                    $message .= ProductHelper::datetotext($product->date) .'
';

                    foreach($product->shoppingcart_product_details as $product_detail)
                    {
                        //Product
                        if($product_detail->type=="product"|| $product_detail->type=="extra")
                        {
                            if($product_detail->unit_price == "Price per booking")
                            {
                                $message .= $product_detail->qty .' '. $product_detail->unit_price .' ('. $product_detail->people .' Person)
';
                            }
                            else
                            {
                                $message .= $product_detail->qty .' '. $product_detail->unit_price .'
';
                            }
                        }
                        elseif($product_detail->type=="pickup")
                        {
                            $message .= $product_detail->title .'
';
                        }

                        $message .= '
';

                        //Question
                        foreach($shoppingcart->shoppingcart_questions()->where('when_to_ask','booking')->where('booking_id',$product->booking_id)->whereNotNull('label')->get() as $shoppingcart_question)
                        {
                                $message .= $shoppingcart_question->label .' : '. $shoppingcart_question->answer .'
';
                        }
                        $participants = $shoppingcart->shoppingcart_questions()->where('when_to_ask','participant')->where('booking_id',$product->booking_id)->select('participant_number')->groupBy('participant_number')->get();
                        foreach($participants as $participant)
                        {
                            $message .= 'Participant '. $participant->participant_number .'
';
                            foreach($shoppingcart->shoppingcart_questions()->where('when_to_ask','participant')->where('booking_id',$product->booking_id)->where('participant_number',$participant->participant_number)->get() as $participant_detail)
                            {
                                $message .= ''.$participant_detail->label .' : '. $participant_detail->answer .'
';
                            }
                        }
        
        
                    }

                    
                    
                    curl_setopt_array($ch = curl_init(), array(
                    CURLOPT_URL => "https://api.pushover.net/1/messages.json",
                    CURLOPT_POSTFIELDS => array(
                        "token" => env('PUSHOVER_TOKEN'),
                        "user" => env('PUSHOVER_USER'),
                        "title" => $title,
                        "message" => $message,
                        //"url" => $url_link,
                        //"url_title" => "View message",
                    ),
                    ));
                    curl_exec($ch);
                    curl_close($ch);
                    
                }
                
        }
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
