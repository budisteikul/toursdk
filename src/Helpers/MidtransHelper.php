<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;

class MidtransHelper {
	
  public static function midtransApiEndpoint()
  {
        if(env('MIDTRANS_ENV')=="production")
        {
            $endpoint = "https://api.midtrans.com/v2/charge";
        }
        else
        {
            $endpoint = "https://api.sandbox.midtrans.com/v2/charge";
        }
        return $endpoint;
  }

  public static function midtransSnapEndpoint()
  {
        if(env('MIDTRANS_ENV')=="production")
        {
            $endpoint = "https://app.midtrans.com";
        }
        else
        {
            $endpoint = "https://app.sandbox.midtrans.com";
        }
        return $endpoint;
  }

  public static function chargeSnap($token,$shoppingcart,$payment_type="other_va")
  {
        

        $email = BookingHelper::get_answer($shoppingcart,'email');
      
        $endpoint = self::midtransSnapEndpoint() ."/snap/v2/transactions/". $token ."/charge";

        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'Authorization' => 'Basic '. base64_encode(env('MIDTRANS_SERVER_KEY')),
          ];

        $data = [
          'customer_details' => [
            'email' => $email,
          ],
          'payment_type' => $payment_type
        ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data,true);

        return $data;
  }

  public static function createOrder($shoppingcart)
  {
        //permata = permata_va
        //bni = bni_va

        //{"customer_details":{"email":"budi@utomo.com","phone":"+6212345678"},"payment_params":{"card_token":"481111-1114-de462335-dec8-4c73-9462-5c344d304328","authentication":"3ds2"},"payment_type":"credit_card"}

        //{"customer_details":{"email":""},"payment_type":"bca_va"}

        $response = new \stdClass();

        $data = MidtransHelper::createSnap($shoppingcart);
        $data2 = MidtransHelper::chargeSnap($data->token,$shoppingcart,"bni_va");

        
        if(isset($data2['permata_va_number']))
        {
          $response->payment_type = $data2['payment_type'];
          $response->bank_name = 'permata';
          $response->bank_code = BookingHelper::get_bankcode($bank_name);
          $response->va_number = $data2['permata_va_number'];
        }
        else
        {
          $response->payment_type = $data2['payment_type'];
          $response->bank_name = $data2['va_numbers'][0]['bank'];
          $response->bank_code = BookingHelper::get_bankcode($data2['va_numbers'][0]['bank']);
          $response->va_number = $data2['va_numbers'][0]['va_number'];
        }

        $response->snaptoken = $data->token;
        return $response;
  }

	public static function createSnap($shoppingcart)
    {
        $first_name = BookingHelper::get_answer($shoppingcart,'firstName');
        $last_name = BookingHelper::get_answer($shoppingcart,'lastName');
        $email = BookingHelper::get_answer($shoppingcart,'email');
        $phone = BookingHelper::get_answer($shoppingcart,'phoneNumber');

        
        $amount = $shoppingcart->due_now;
        $order_id = $shoppingcart->confirmation_code;

        $endpoint = self::midtransSnapEndpoint() ."/snap/v1/transactions";

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
            ],
            'callbacks' => [
              'finish' => '',
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