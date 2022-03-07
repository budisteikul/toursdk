<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;


class OyHelper {
	
  public static function env_oyApiKey()
  {
        return env("OY_API_KEY");
  }

  public static function env_oyUsername()
  {
        return env("OY_USERNAME");
  }

  public static function env_oyEnv()
  {
        return env("OY_ENV");
  }

  public static function env_appName()
  {
        return env("APP_NAME");
  }

  public static function env_oyUseProxy()
  {
        return env("OY_USE_PROXY",false);
  }

  public static function env_proxyServer()
  {
        return env("PROXY_SERVER");
  }

  public static function env_proxyUsermame()
  {
        return env("PROXY_USERNAME");
  }

  public static function env_proxyPassword()
  {
        return env("PROXY_PASSWORD");
  }

  public static function env_proxyPort()
  {
        return env("PROXY_PORT");
  }

  public static function oyUseProxy()
  {
      $proxy = null;
      if(self::env_oyUseProxy())
      {
            $proxy = 'http://'. self::env_proxyUsermame() .':'. self::env_proxyPassword() .'@'. self::env_proxyServer() .':'. self::env_proxyPort();
      }
      return $proxy;
  }

  public static function oyApiEndpoint()
  {
        if(self::env_oyEnv()=="production")
        {
            $endpoint = "https://partner.oyindonesia.com";
        }
        else
        {
            $endpoint = "https://api-stg.oyindonesia.com";
        }
        return $endpoint;
  }

  public static function oyLink($token)
  {
        if(self::env_oyEnv()=="production")
        {
            $endpoint = "https://pay.oyindonesia.com/". $token;
        }
        else
        {
            $endpoint = "https://pay-stg.oyindonesia.com/". $token;
        }
        return $endpoint;
  }

  public static function oyCheckoutEndpoint()
  {
        if(self::env_oyEnv()=="production")
        {
            $endpoint = "https://checkout.oyindonesia.com";
        }
        else
        {
            $endpoint = "https://checkout-stg.oyindonesia.com";
        }
        return $endpoint;
  }

  public static function createDisbursement($disbursement)
  {
        $endpoint = self::oyApiEndpoint() ."/api/remit";
        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'x-oy-username' => self::env_oyUsername(),
              'x-api-key' => self::env_oyApiKey(),
          ];

        $data = [
          'recipient_bank' => $disbursement->bank_code,
          'recipient_account' => $disbursement->account_number,
          'amount' => $disbursement->amount,
          'note' => $disbursement->reference,
          'partner_trx_id' => $disbursement->transaction_id,
        ];

        
        
        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          [
            'json' => $data,
            'proxy' => self::oyUseProxy()
          ]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        
        return $data;
       
  }

  public static function bankCode($bank)
  {
    $data = new \stdClass();
    switch($bank)
    {
      case "btpn":
        $data->bank_name = "Jenius (BTPN)";
        $data->bank_code = "213";
        $data->bank_payment_type = "btpn";
      break;
      case "bri":
        $data->bank_name = "bri";
        $data->bank_code = "002";
        $data->bank_payment_type = "bri";
      break;
      case "cimb":
        $data->bank_name = "cimb niaga";
        $data->bank_code = "022";
        $data->bank_payment_type = "cimb";
      break;
      case "mandiri":
        $data->bank_name = "mandiri";
        $data->bank_code = "008";
        $data->bank_payment_type = "mandiri";
      break;
      case "permata":
        $data->bank_name = "permata";
        $data->bank_code = "013";
        $data->bank_payment_type = "permata";
      break;
      case "bni":
        $data->bank_name = "bni";
        $data->bank_code = "009";
        $data->bank_payment_type = "bni";
      break;
      case "shopeepay":
        $data->bank_name = "shopeepay";
        $data->bank_code = "";
        $data->bank_payment_type = "shopeepay_ewallet";
      break;
      case "qris":
        $data->bank_name = "shopeepay";
        $data->bank_code = "";
        $data->bank_payment_type = "qris_shopee";
      break;
    }
    return $data;
  }

  

  public static function createPayment($data)
  {
        $response = new \stdClass();

        $payment = self::bankCode($data->transaction->bank);

        if($payment->bank_payment_type=="qris_shopee")
        {
          $data1 = self::createSnap($data);
          $data2 = self::createCharge($data,$data1->snaptoken,$payment);
          
          $qrcode = ImageHelper::uploadQrcodeCloudinary($data2->data->qris_url);
          $qrcode_url = $qrcode['secure_url'];

          //$path = date('Y-m-d');
          //$contents = file_get_contents($data2->data->qris_url);
          //Storage::put('qrcode/'. $path .'/'.$data1->snaptoken.'.png', $contents);
          //$qrcode_url = Storage::url('qrcode/'. $path .'/'.$data1->snaptoken.'.png');

          $response->payment_type = 'ewallet';
          $response->bank_name = $payment->bank_name;
          $response->qrcode = $qrcode_url;
          $response->link = self::oyLink($data1->snaptoken);
        }
        else
        {
          $data1 = self::createSnap($data);
          $data2 = self::createCharge($data,$data1->snaptoken,$payment);
          $data3 = self::status($data1->snaptoken);
          
          $response->payment_type = 'bank_transfer';
          $response->bank_name = $payment->bank_name;
          $response->bank_code = $data3->data->sender_bank;
          $response->va_number = $data3->data->va_number;
          $response->link = self::oyLink($data1->snaptoken);
        }
        
        
        return $response;
  }

  public static function status($token)
  {
        $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/status/enc/'. $token;

        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json'
          ];

        
        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('GET',$endpoint,[ 'proxy' => self::oyUseProxy() ]);
        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        return $data;
  }

  public static function createCharge($data,$token,$payment)
  {
        if($payment->bank_payment_type=="qris_shopee")
        {
            $data = [
                'tx_id' => $token,
                'amount' => $data->transaction->amount,
                'sender_name' => $data->contact->name,
                'sender_phone' => NULL,
                'sender_notes' => NULL,
                'sender_email' => NULL,
                'email_active' => false,
                'card_sender' => $payment->bank_payment_type
            ];

            $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/qris/create';
            $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json'
          ];

         
          $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
          $response = $client->request('POST',$endpoint,
              [
                'json' => $data,
                'proxy' => self::oyUseProxy()
              ]
          );

          $data = $response->getBody()->getContents();
          $data = json_decode($data);

          
        }
        else if($payment->bank_payment_type=="shopeepay_ewallet")
        {
            $data = [
                'tx_id' => $token,
                'amount' => $data->transaction->amount,
                'admin_fee' => 0,
                'sender_phone' => NULL,
                'sender_notes' => NULL,
                'sender_email' => NULL,
                'sender_name' => $data->contact->name,
                'ewallet_type' => $payment->bank_payment_type,
                'email_active' => false
            ];

            $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/enc/ewallet/create';

            $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json'
            ];

            
            $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
            $response = $client->request('POST',$endpoint,
              [
                'json' => $data,
                'proxy' => self::oyUseProxy()
              ]
            );

            $data = $response->getBody()->getContents();
            $data = json_decode($data);
        }
        else
        {
            $data = [
                'amount' => $data->transaction->amount,
                'admin_fee' => 0,
                'sender_phone' => NULL,
                'sender_notes' => NULL,
                'sender_email' => NULL,
                'sender_name' => $data->contact->name,
                'card_sender' => $payment->bank_code,
                'device_id' => NULL,
                'payment_method' => 'VA',
                'email_active' => false
            ];

            $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/enc/'. $token;

            $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json'
            ];

            
            $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
            $response = $client->request('PUT',$endpoint,
              [
                'json' => $data,
                'proxy' => self::oyUseProxy()
              ]
            );

            $data = $response->getBody()->getContents();
            $data = json_decode($data);
            
        }
        return $data;
  }

  public static function createSnap($data)
  {
        $endpoint = self::oyApiEndpoint() ."/api/payment-checkout/create-v2";
        $headers = [
              'Cache-Control' => 'no-cache',
              'Content-Type' => 'application/json',
              'x-oy-username' => self::env_oyUsername(),
              'x-api-key' => self::env_oyApiKey(),
          ];

        $data = [
          'partner_tx_id' => $data->transaction->id,
          'sender_name' => $data->contact->name,
          'amount' => $data->transaction->amount,
          'email' => null,
          'phone_number' => null,
          'username_display' => self::env_appName(),
          'is_open' => false,
          'list_disabled_payment_methods' => "EWALLET,CREDIT_CARD",
          'expiration' => $data->transaction->date_expired,
          'due_date' => $data->transaction->date_expired,
        ];

        
        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          [
            'json' => $data,
            'proxy' => self::oyUseProxy()
          ]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);

        $response = new \stdClass();
        $response->snaptoken = $data->payment_link_id;
        $response->link = $data->url;

        return $response;
  }

  
	
	
}
?>