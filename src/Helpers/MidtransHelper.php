<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\coresdk\Helpers\GeneralHelper;

class MidtransHelper {
	
	public static function createOrder($shoppingcart)
    {

        $first_name = $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','firstName')->first()->answer;
        $last_name = $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','lastName')->first()->answer;
        $email = $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','email')->first()->answer;
        $phone = $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','phoneNumber')->first()->answer;

        $amount = GeneralHelper::roundCurrency($shoppingcart->due_now,"IDR");
        $order_id = $shoppingcart->confirmation_code;

        if(env('MIDTRANS_ENV')=="sandbox")
        {
            $endpoint = "https://app.sandbox.midtrans.com/snap/v1/transactions";
        }
        else
        {
          $endpoint = "https://app.midtrans.com/snap/v1/transactions";
        }

        $data = [
            'transaction_details' => [
              'order_id' => $order_id,
              'gross_amount' => $amount
            ],
            'customer_details' => [
              'first_name' => $first_name,
              'last_name' => $last_name,
              'email' => $email,
              'phone' => $phone
            ]
          ];

        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'Authorization' => 'Basic '. base64_encode(env('MIDTRANS_SERVER_KEY')),
          ];
        
        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        return $data;
    }
	
	
}
?>