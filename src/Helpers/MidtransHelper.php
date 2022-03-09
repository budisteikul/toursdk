<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;

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

  public static function bankCode($bank)
    {
        $data = new \stdClass();
        switch($bank)
        {
            case "mandiri":
                $data->bank_name = "mandiri bill";
                $data->bank_code = "";
                $data->bank_payment_type = "echannel";
            break;
            case "permata":
                $data->bank_name = "permata";
                $data->bank_code = "013";
                $data->bank_payment_type = "permata_va";
            break;
            case "bni":
                $data->bank_name = "bni";
                $data->bank_code = "009";
                $data->bank_payment_type = "bni_va";
            break;
            case "gopay":
                $data->bank_name = "gopay";
                $data->bank_code = "";
                $data->bank_payment_type = "gopay";
            break;
            case "qris_gopay":
                $data->bank_name = "qris (gopay)";
                $data->bank_code = "";
                $data->bank_payment_type = "gopay";
            break;
        }

        return $data;
    }

  

  public static function createPayment($data)
  {
        $payment = self::bankCode($data->transaction->bank);

        $data1 = MidtransHelper::createSnap($data,$payment);
        $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);

        $response = new \stdClass();
        if($payment->bank_payment_type=="permata_va")
        {
          $response->payment_type = 'bank_transfer';
          $response->bank_name = $payment->bank_name;
          $response->bank_code = $payment->bank_code;
          $response->va_number = $data2['permata_va_number'];
        }
        else if($payment->bank_payment_type=="gopay")
        {
          $qrcode = ImageHelper::uploadQrcodeCloudinary($data2['qr_code_url']);
          $qrcode_url = $qrcode['secure_url'];

          //$path = date('Y-m-d');
          //$contents = file_get_contents($data2['qr_code_url']);
          //Storage::put('qrcode/'. $path .'/'.$data1->token.'.png', $contents);
          //$qrcode_url = Storage::url('qrcode/'. $path .'/'.$data1->token.'.png');

          $response->bank_name = $payment->bank_name;
          $response->qrcode = $qrcode_url;
          $response->link = $data2['deeplink_url'];

          if($data->transaction->bank=="qris_gopay")
          {
            $response->payment_type = 'qris';
            $response->redirect = $data->transaction->finish_url;
          }
          else
          {
            $response->payment_type = 'ewallet';
            $response->redirect = $data2['deeplink_url'];
          }
          
        }
        else if($payment->bank_payment_type=="echannel")
        {
          $response->payment_type = 'bank_transfer';
          $response->bank_name = $payment->bank_name;
          $response->bank_code = $data2['biller_code'];
          $response->va_number = $data2['bill_key'];
          $response->redirect = $data->transaction->finish_url;
        }
        else
        {
          $response->payment_type = 'bank_transfer';
          $response->bank_name = $payment->bank_name;
          $response->bank_code = $payment->bank_code;
          $response->va_number = $data2['va_numbers'][0]['va_number'];
          $response->redirect = $data->transaction->finish_url;
        }

        $response->snaptoken = $data1->token;
        return $response;
  }

	public static function createSnap($data,$payment)
    {
        
        $endpoint = self::midtransSnapEndpoint() ."/snap/v1/transactions";

        $data_post = [
            'transaction_details' => [
              'order_id' => $data->transaction->id,
              'gross_amount' => $data->transaction->amount
            ],
            'customer_details' => [
              'first_name' => $data->contact->first_name,
              'last_name' => $data->contact->last_name,
              'email' => $data->contact->email,
              'phone' => $data->contact->phone
            ],
            'expiry'=> [
              'start_time' => $data->transaction->date_now,
              'unit' => 'minutes',
              'duration' => $data->transaction->mins_expired
            ],
            'callbacks' => [
              'finish' => '',
            ]
          ];

        if($payment->bank_payment_type=="permata_va")
        {
            $data_permata = [
              'permata_va' => [
                'recipient_name' => $data->contact->name
              ]
            ];

            $data_post = array_merge($data_post,$data_permata);
        }

        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'Authorization' => 'Basic '. base64_encode(self::env_midtransServerKey()),
          ];
        
        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data_post]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        return $data;
    }
	
	public static function chargeSnap($token,$data,$payment)
  {
        $data = [
              'customer_details' => [
                'email' => $data->contact->email,
               ],
              'payment_type' => $payment->bank_payment_type
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
}
?>
