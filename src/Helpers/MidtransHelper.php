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

  public static function chargeSnap($token,$shoppingcart,$bank)
  {

        if($bank=="permata")
        {
          $payment_type = "permata_va";
        }
        else if($bank=="gopay")
        {
          $payment_type = "gopay";
        }
        else if($bank=="bni")
        {
          $payment_type = "bni_va";
        }
        else if($bank=="mandiri")
        {
          $payment_type = "echannel";
        }
        else
        {
          return "";
        }

        $data = [
              'customer_details' => [
                'email' => BookingHelper::get_answer($shoppingcart,'email'),
               ],
              'payment_type' => $payment_type
            ];
      
        $endpoint = self::midtransSnapEndpoint() ."/snap/v2/transactions/". $token ."/charge";

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
        $data = json_decode($data,true);

        return $data;
  }

  public static function createOrder($shoppingcart,$bank)
  {
        //permata = permata_va
        //bni = bni_va

        $response = new \stdClass();

        $data = MidtransHelper::createSnap($shoppingcart,$bank);
        $data2 = MidtransHelper::chargeSnap($data->token,$shoppingcart,$bank);

        if($bank=="permata")
        {
          $response->payment_type = 'bank_transfer';
          $response->bank_name = 'permata';
          $response->bank_code = '013';
          $response->va_number = $data2['permata_va_number'];
        }
        else if($bank=="gopay")
        {
          $qrcode = ImageHelper::uploadQrcodeCloudinary($data2['qr_code_url']);
          $response->payment_type = 'ewallet';
          $response->bank_name = 'gopay';
          $response->qrcode = $qrcode['secure_url'];
          $response->link = $data2['deeplink_url'];
        }
        else if($bank=="mandiri")
        {
          $response->payment_type = 'bank_transfer';
          $response->bank_name = 'mandiri';
          $response->bank_code = '008';
          $response->va_number = $data2['biller_code'].$data2['bill_key'];
        }
        else if($bank=="bni")
        {
          $response->payment_type = 'bank_transfer';
          $response->bank_name = 'bni';
          $response->bank_code = '009';
          $response->va_number = $data2['va_numbers'][0]['va_number'];
        }
        else
        {
          return "";
        }

        $response->snaptoken = $data->token;
        return $response;
  }

	public static function createSnap($shoppingcart,$bank)
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

        if($bank=="permata")
        {
            $data_permata = [
              'permata_va' => [
                'recipient_name' => $first_nama .' '. $last_name
              ]
            ];

            $data = array_merge($data,$data_permata);
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
