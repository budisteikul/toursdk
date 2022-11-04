<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Zxing\QrReader;

class MidtransHelper {
	
  public static function env_appUrl()
  {
        return env("APP_URL");
  }

  public static function env_appApiUrl()
  {
        return env("APP_API_URL");
  }

  public static function env_midtransServerKey()
  {
        return env("MIDTRANS_SERVER_KEY");
  }

  public static function env_midtransApiKey()
  {
        return env("MIDTRANS_API_KEY");
  }

  public static function midtransApiEndpoint()
  {
        if(env('MIDTRANS_ENV')=="production")
        {
            $endpoint = "https://app.midtrans.com/iris";
        }
        else
        {
            $endpoint = "https://app.sandbox.midtrans.com/iris";
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
            case "bri":
                $data->bank_name = "bri";
                $data->bank_code = "002";
                $data->bank_payment_type = "bri_va";
            break;
            case "bca":
                $data->bank_name = "bca";
                $data->bank_code = "014";
                $data->bank_payment_type = "bca_va";
            break;
            case "gopay":
                $data->bank_name = "gopay";
                $data->bank_code = "";
                $data->bank_payment_type = "gopay";
            break;
            case "shopeepay":
                $data->bank_name = "shopeepay";
                $data->bank_code = "";
                $data->bank_payment_type = "shopeepay";
            break;
            case "gopay_qris":
                $data->bank_name = "gopay";
                $data->bank_code = "";
                $data->bank_payment_type = "qris";
            break;
            case "shopeepay_qris":
                $data->bank_name = "shopeepay";
                $data->bank_code = "";
                $data->bank_payment_type = "qris";
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
        $data_json = new \stdClass();
        $status_json = new \stdClass();
        $response_json = new \stdClass();

        $payment = self::bankCode($data->transaction->bank);

        if($payment->bank_payment_type=="permata_va")
        {
          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);

          $data_json->payment_type = 'bank_transfer';
          $data_json->bank_name = $payment->bank_name;
          $data_json->bank_code = $payment->bank_code;
          $data_json->va_number = $data2['permata_va_number'];
          $data_json->expiration_date = $data->transaction->date_expired;
          $data_json->order_id = $data->transaction->id;
          $data_json->redirect = $data->transaction->finish_url;
        }
        else if($payment->bank_payment_type=="gopay")
        {
          $data->transaction->mins_expired = 60;
          $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);
          
          $data_json->bank_name = $payment->bank_name;
          $data_json->link = null;
          $data_json->expiration_date = $data->transaction->date_expired;
          $data_json->order_id = $data->transaction->id;
          $data_json->payment_type = 'ewallet';
          //$response->redirect = str_ireplace("gojek://","https://gojek.link/",$data2['deeplink_url']);
          $data_json->redirect = $data2['deeplink_url'];
        }
        else if($payment->bank_payment_type=="qris")
        {
          $data->transaction->mins_expired = 60;
          $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);
          
          $data_json->bank_name = $payment->bank_name;
          $data_json->qrcode = $data2['qr_string'];
          $data_json->link = null;
          $data_json->expiration_date = $data->transaction->date_expired;
          $data_json->order_id = $data->transaction->id;

          $data_json->payment_type = 'qrcode';
          $data_json->redirect = $data->transaction->finish_url;
        }
        else if($payment->bank_payment_type=="shopeepay")
        {
          $data->transaction->mins_expired = 60;
          $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);
          
          $data_json->bank_name = $payment->bank_name;
          $data_json->link = null;
          $data_json->expiration_date = $data->transaction->date_expired;
          $data_json->order_id = $data->transaction->id;

          $data_json->payment_type = 'ewallet';
          $data_json->redirect = $data2['deeplink_url'];
        }
        else if($payment->bank_payment_type=="echannel")
        {
          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);

          $data_json->payment_type = 'bank_transfer';
          $data_json->bank_name = $payment->bank_name;
          $data_json->bank_code = $data2['biller_code'];
          $data_json->va_number = $data2['bill_key'];
          $data_json->redirect = $data->transaction->finish_url;
          $data_json->expiration_date = $data->transaction->date_expired;
          $data_json->order_id = $data->transaction->id;
        }
        else
        {
          $data1 = MidtransHelper::createSnap($data,$payment);
          $data2 = MidtransHelper::chargeSnap($data1->token,$data,$payment);

          $data_json->payment_type = 'bank_transfer';
          $data_json->bank_name = $payment->bank_name;
          $data_json->bank_code = $payment->bank_code;
          $data_json->va_number = $data2['va_numbers'][0]['va_number'];
          $data_json->redirect = $data->transaction->finish_url;
          $data_json->expiration_date = $data->transaction->date_expired;
          $data_json->order_id = $data->transaction->id;
        }

        $data_json->authorization_id = $data1->token;

        $status_json->id = '1';
        $status_json->message = 'success';
        
        $response_json->status = $status_json;
        $response_json->data = $data_json;
        
        return $response_json;
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
              //'phone' => $data->contact->phone
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
              'X-Override-Notification' => self::env_appApiUrl() .'/payment/midtrans/confirm',
              'Authorization' => 'Basic '. base64_encode(self::env_midtransServerKey()),
          ];
        
        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data_post]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        print_r($data);
        exit();
        return $data;
    }
	
	public static function chargeSnap($token,$data,$payment)
  {
        $data = [
              'customer_details' => [
                'email' => $data->contact->email,
               ]
            ];
        
        if($payment->bank_payment_type=="qris")
        {
                $data_payment_type = [
                  'payment_type' => $payment->bank_payment_type,
                  'payment_params' => [
                      'acquirer' => [$payment->bank_name]
                  ]
                ];
        }
        else
        {
              $data_payment_type = [
                'payment_type' => $payment->bank_payment_type
              ];
        }
        

        $data = array_merge($data,$data_payment_type);

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

  public static function checkSignature($request)
  {
      $status = false;
      $data = $request->all();
      $signature = null;
      
      if(isset($data['signature_key'])) $signature = $data['signature_key'];
      if(hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].self::env_midtransServerKey())==$signature)
      {
        $status = true;
      }
      return $status;
  }

}
?>
