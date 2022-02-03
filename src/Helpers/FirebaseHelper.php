<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;

class FirebaseHelper {

    public static function env_firebaseDatabaseUrl()
    {
        return env("FIREBASE_DATABASE_URL");
    }

    public static function env_firebaseDatabaseSecret()
    {
        return env("FIREBASE_DATABASE_SECRET");
    }

	public static function delete($shoppingcart)
	{
		$endpoint = "https://". self::env_firebaseDatabaseUrl() ."/". $shoppingcart->id .".json?auth=". self::env_firebaseDatabaseSecret();
  		$client = new \GuzzleHttp\Client(['http_errors' => false]);
        $response = $client->request('DELETE',$endpoint);

        $data = $response->getBody()->getContents();
        $data = json_decode($data,true);
	}

	public static function upload($shoppingcart,$index="")
  	{
        if($index=="") $index = "receipt";

        if($index=="receipt")
        {
            $invoice = 'No Documents';
            try {
                if($shoppingcart->shoppingcart_payment->payment_status>0) {
                    $invoice = '<a target="_blank" class="text-theme" href="'.url('/api').'/pdf/invoice/'. $shoppingcart->session_id .'/Invoice-'. $shoppingcart->confirmation_code .'.pdf"><i class="fas fa-file-invoice"></i> Invoice-'. $shoppingcart->confirmation_code .'.pdf</a><br />';
                }
            } catch (Exception $e) {
            }

            $ticket = '';
            try {
                if($shoppingcart->shoppingcart_payment->payment_status==2 || $shoppingcart->shoppingcart_payment->payment_status==1) {
                    foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product) {
                    $ticket .= '<a target="_blank" class="text-theme" href="'.url('/api').'/pdf/ticket/'.$shoppingcart->session_id.'/Ticket-'.$shoppingcart_product->product_confirmation_code.'.pdf"><i class="fas fa-ticket-alt"></i> Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf</a>
                                <br />';
                    }
                }
            } catch (Exception $e) {
            }
        
            if($ticket=="") $ticket = 'No Documents <br /><small class="form-text text-muted">* Available when status is paid</small>';

        
            $pdfUrl = array();
        
            if($shoppingcart->shoppingcart_payment->payment_provider=="midtrans") {
                if($shoppingcart->shoppingcart_payment->payment_type=="ewallet")
                {
                    $pdfUrl = '
                    <div class="pl-2">
                    1.  Open your <b>E-wallet</b> or <b>Mobile Banking</b> apps. <br />
                    2.  <b>Scan</b> the QR code shown on your monitor. <br />
                    <img width="230" class="mt-2 mb-2" src="'. env('APP_URL') .'/img/qr-instruction.png">
                    <br />
                    3.  Check your payment details in the app, then tap <b>Pay</b>. <br />
                    4.  Enter your <b>PIN</b>. <br />
                    5.  Your transaction is complete. 
                    </div>';
                }
                else
                {
                    $pdfUrl = '<a target="_blank" class="text-theme" href="'.url('/api').'/pdf/instruction/'. $shoppingcart->session_id .'/Instruction-'. $shoppingcart->confirmation_code .'.pdf"><i class="fas fa-file-invoice"></i> Instruction-'. $shoppingcart->confirmation_code .'.pdf</a><br />';
                }
            
            }

            $payment_status_asText = BookingHelper::get_paymentStatus($shoppingcart);
            $booking_status_asText = BookingHelper::get_bookingStatus($shoppingcart);

            $main_contact = BookingHelper::get_answer_contact($shoppingcart);
        
            $dataObj = array(
            'vendor' => env('APP_NAME'),
            'booking_status' => $shoppingcart->booking_status,
            'booking_status_asText' => $booking_status_asText,
            'confirmation_code' => $shoppingcart->confirmation_code,
            'total' => $shoppingcart->currency .' '. GeneralHelper::numberFormat($shoppingcart->due_now),
            'payment_status' => $shoppingcart->shoppingcart_payment->payment_status,
            'payment_status_asText' => $payment_status_asText,
            'firstName' => $main_contact->firstName,
            'lastName' => $main_contact->lastName,
            'phoneNumber' => $main_contact->phoneNumber,
            'email' => $main_contact->email,
            'invoice' => $invoice,
            'tickets' => $ticket,
            'paymentProvider' => $shoppingcart->shoppingcart_payment->payment_provider,
            'pdf_url' => $pdfUrl,
            );

            $data = array(
            'receipt' => $dataObj
            );
        

            $endpoint = "https://". self::env_firebaseDatabaseUrl() ."/receipt/". $shoppingcart->session_id ."/". $shoppingcart->id .".json?auth=". self::env_firebaseDatabaseSecret();
            $client = new \GuzzleHttp\Client(['http_errors' => false]);
            $response = $client->request('PUT',$endpoint,
            ['body' => json_encode($data)]
            );

            $data = $response->getBody()->getContents();
            $data = json_decode($data,true);
        }
  		
  	}
}
?>