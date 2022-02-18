<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\ImageHelper;
use Carbon\Carbon;

class MidtransHelper {
	
  public static function env_midtransServerKey()
  {
        return env("MIDTRANS_SERVER_KEY");
  }

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
              'Authorization' => 'Basic '. base64_encode(self::env_midtransServerKey()),
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
        $date_arr = array();
        foreach($shoppingcart->products as $product)
        {
            $date_arr[] = $product->date;
            
        }

        usort($date_arr, function($a, $b) {

            $dateTimestamp1 = strtotime($a);
            $dateTimestamp2 = strtotime($b);

            return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
        });

        $date1 = Carbon::now();
        $date2 = Carbon::parse($date_arr[0]);
        $mins  = $date2->diffInMinutes($date1, true);
        if($mins<=15) $mins = 15;

        $date_now = Carbon::parse($date1)->formatLocalized('%Y-%m-%d %H:%M:%S +0700');

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
            'expiry'=> [
              'start_time' => $date_now,
              'unit' => 'minutes',
              'duration' => $mins
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
            'expiry'=> [
              'start_time' => $date_now,
              'unit' => 'minutes',
              'duration' => $mins
            ],
            'callbacks' => [
              'finish' => '',
            ]
          ];
        }

        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'Authorization' => 'Basic '. base64_encode(self::env_midtransServerKey()),
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