<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;
use budisteikul\toursdk\Helpers\FirebaseHelper;
use Carbon\Carbon;

class MidtransHelper {
	
  public static function env_appUrl()
  {
        return env("APP_URL");
  }

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

  public static function env_midtransNmid()
    {
        return env("MIDTRANS_NMID","");
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
            case "qris":
                $data->bank_name = "gopay";
                $data->bank_code = "";
                $data->bank_payment_type = "gopay";
            break;
            default:
                return response()->json([
                    "message" => 'Error'
                ]);
        }

        return $data;
    }

  

  public static function createPayment($data)
  {
        $payment = self::bankCode($data->transaction->bank);

        $response = new \stdClass();
        if($payment->bank_payment_type=="permata_va")
        {
          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);

          $response->payment_type = 'bank_transfer';
          $response->bank_name = $payment->bank_name;
          $response->bank_code = $payment->bank_code;
          $response->va_number = $data2['permata_va_number'];
          $response->expiration_date = $data->transaction->date_expired;
          $response->order_id = $data->transaction->id;
        }
        else if($payment->bank_payment_type=="gopay")
        {
          $data->transaction->mins_expired = 120;
          $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);

         
          //$qrcode = ImageHelper::uploadQrcodeCloudinary($data2['qr_code_url']);
          //$qrcode_url = $qrcode['secure_url'];

          $path = date('Y-m-d');
          $contents = file_get_contents($data2['qr_code_url']);
          Storage::disk('gcs')->put('qrcode/'. $path .'/'.$data1->token.'.png', $contents);
          $qrcode_url = Storage::disk('gcs')->url('qrcode/'. $path .'/'.$data1->token.'.png');

          $response->bank_name = $payment->bank_name;
          $response->qrcode = $qrcode_url;
          $response->link = null;
          $response->expiration_date = $data->transaction->date_expired;
          $response->order_id = $data->transaction->id;

          if($data->transaction->bank=="qris")
          {
            $response->payment_type = 'qris';
            $response->bank_code = self::env_midtransNmid();
            $response->redirect = $data->transaction->finish_url;
          }
          else
          {
            $response->payment_type = 'ewallet';
            $response->redirect = str_ireplace("gojek://","https://gojek.link/",$data2['deeplink_url']);
          }
          
        }
        else if($payment->bank_payment_type=="echannel")
        {

          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);

          $response->payment_type = 'bank_transfer';
          $response->bank_name = $payment->bank_name;
          $response->bank_code = $data2['biller_code'];
          $response->va_number = $data2['bill_key'];
          $response->redirect = $data->transaction->finish_url;
          $response->expiration_date = $data->transaction->date_expired;
          $response->order_id = $data->transaction->id;
        }
        else
        {
          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);

          $response->payment_type = 'bank_transfer';
          $response->bank_name = $payment->bank_name;
          $response->bank_code = $payment->bank_code;
          $response->va_number = $data2['va_numbers'][0]['va_number'];
          $response->redirect = $data->transaction->finish_url;
          $response->expiration_date = $data->transaction->date_expired;
          $response->order_id = $data->transaction->id;
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
              'finish' => self::env_appUrl() . $data->transaction->finish_url,
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

        if($payment->bank_payment_type=="gopay")
        {
            $data_gopay = [
              'gopay' => [
                'enable_callback' => true,
                'callback_url' => self::env_appUrl() . $data->transaction->finish_url,
              ]
            ];

            $data_post = array_merge($data_post,$data_gopay);
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
