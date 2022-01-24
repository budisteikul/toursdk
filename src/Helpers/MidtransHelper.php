<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\ImageHelper;

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

  public static function createOrder($shoppingcart,$payment_type)
  {
        //permata = permata_va
        //bni = bni_va

        $response = new \stdClass();

        $data = MidtransHelper::createSnap($shoppingcart,$payment_type);
        $data2 = MidtransHelper::chargeSnap($data->token,$shoppingcart,$payment_type);

        if(isset($data2['permata_va_number']))
        {
          $response->payment_type = 'bank_transfer';
          $response->bank_name = 'permata';
          $response->bank_code = BookingHelper::get_bankcode('permata');
          $response->va_number = $data2['permata_va_number'];
        }
        else if($data2['payment_type']=="gopay")
        {

          $qrcode = ImageHelper::uploadQrcodeCloudinary($data2['qr_code_url']);

          $response->payment_type = 'ewallet';
          $response->bank_name = 'gopay';
          $response->qrcode = $qrcode['secure_url'];
          $response->link = $data2['deeplink_url'];
        }
        else
        {
          $response->payment_type = 'bank_transfer';
          $response->bank_name = 'bni';
          $response->bank_code = BookingHelper::get_bankcode('bni');
          $response->va_number = $data2['va_numbers'][0]['va_number'];
        }

        $response->snaptoken = $data->token;
        return $response;
  }

	public static function createSnap($shoppingcart,$payment_type)
    {
        $first_name = BookingHelper::get_answer($shoppingcart,'firstName');
        $last_name = BookingHelper::get_answer($shoppingcart,'lastName');
        $email = BookingHelper::get_answer($shoppingcart,'email');
        $phone = BookingHelper::get_answer($shoppingcart,'phoneNumber');

        
        $amount = $shoppingcart->due_now;
        $order_id = $shoppingcart->confirmation_code;

        $endpoint = self::midtransSnapEndpoint() ."/snap/v1/transactions";

        if($payment_type=="permata_va")
        {
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
            'permata_va' => [
              'recipient_name' => $first_name .' '. $last_name
            ],
            'callbacks' => [
              'finish' => '',
            ]
          ];
        }
        else
        {
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
        }

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