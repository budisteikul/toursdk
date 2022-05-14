<?php
namespace budisteikul\toursdk\Helpers;
use Carbon\Carbon;

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

  public static function env_appUrl()
  {
        return env("APP_URL");
  }

  public static function env_oyUseProxy()
  {
        return env("OY_USE_PROXY",false);
  }

  public static function env_proxyServer()
  {
        return env("PROXY_SERVER");
  }

  public static function env_proxyUsername()
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
            $proxy = 'http://'. self::env_proxyUsername() .':'. self::env_proxyPassword() .'@'. self::env_proxyServer() .':'. self::env_proxyPort();
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
      case "linkaja":
        $data->bank_name = "linkaja";
        $data->bank_code = "";
        $data->bank_payment_type = "linkaja_ewallet";
      break;
      case "dana":
        $data->bank_name = "dana";
        $data->bank_code = "";
        $data->bank_payment_type = "dana_ewallet";
      break;
      case "qris":
        $data->bank_name = "shopeepay";
        $data->bank_code = "";
        $data->bank_payment_type = "qris_shopee";
      break;
      default:
        return response()->json([
          "message" => 'Error'
        ]);
    }
    return $data;
  }

  public static function createEwallet($data)
  {
        $endpoint = self::oyApiEndpoint() ."/api/e-wallet-aggregator/create-transaction";
        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'x-oy-username' => self::env_oyUsername(),
              'x-api-key' => self::env_oyApiKey(),
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

  public static function createVA($data)
  {
        $endpoint = self::oyApiEndpoint() ."/api/generate-static-va";
        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'x-oy-username' => self::env_oyUsername(),
              'x-api-key' => self::env_oyApiKey(),
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

  public static function createPayment($data)
  {
        $payment = self::bankCode($data->transaction->bank);

        $response = new \stdClass();

        if($payment->bank_payment_type=="qris_shopee")
        {
          
          $data->transaction->mins_expired = 120;
          $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired)->formatLocalized('%Y-%m-%d %H:%M:%S');

          $data1 = self::createSnap($data);
          $data2 = self::createCharge($data,$data1->authorization_id,$payment);

          $response->payment_type = 'qris';
          $response->bank_name = $payment->bank_name;
          $response->qrcode = $data2->data->qris_content;
          $response->authorization_id = $data1->authorization_id;
          $response->link = self::oyLink($data1->authorization_id);
          $response->redirect = $data->transaction->finish_url;
          $response->expiration_date = $data->transaction->date_expired;
          $response->order_id = $data->transaction->id;
        }
        else if($payment->bank_payment_type=="shopeepay_ewallet" || $payment->bank_payment_type=="linkaja_ewallet" || $payment->bank_payment_type=="dana_ewallet")
        {
          
          $data->transaction->mins_expired = 60;

          $init_data = [
            'customer_id' => $data->transaction->id,
            'partner_trx_id' => $data->transaction->id,
            'amount' => $data->transaction->amount,
            'email' => null,
            'ewallet_code' => $payment->bank_payment_type,
            'mobile_number' => null,
            'success_redirect_url' => self::env_appUrl() . $data->transaction->finish_url,
            'expiration_time' => $data->transaction->mins_expired,
          ];

          $data1 = self::createEwallet($init_data);
          
          $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

          $ewallet_url = $data1->ewallet_url;

          if($payment->bank_payment_type=="dana_ewallet")
          {
              $ewallet_url = str_replace("https://link.dana.id/pay","https://m.dana.id/m/portal/cashier/checkout",$ewallet_url);
          }

          if($payment->bank_payment_type=="linkaja_ewallet")
          {
              $ewallet_url = str_replace("linkaja://","https://linkaja.id/",$ewallet_url);
          }

          $response->payment_type = 'ewallet';
          $response->bank_name = $payment->bank_name;
          $response->bank_code = null;
          $response->va_number = null;
          $response->authorization_id = null;
          $response->link = null;
          $response->redirect = $ewallet_url;
          $response->expiration_date = $data->transaction->date_expired;
          $response->order_id = $data->transaction->id;
        }
        else
        {

          $init_data = [
            'partner_user_id' => $data->transaction->id,
            'bank_code' => $payment->bank_code,
            'amount' => $data->transaction->amount,
            'is_open' => false,
            'is_single_use' => true,
            'is_lifetime' => false,
            'expiration_time' => $data->transaction->mins_expired,
            'username_display' => $data->contact->first_name,
            'email' => null,
            'partner_trx_id' => $data->transaction->id,
            'trx_counter' => 1,
          ];


          $data1 = self::createVA($init_data);

          $response->payment_type = 'bank_transfer';
          $response->bank_name = $payment->bank_name;
          $response->bank_code = $data1->bank_code;
          $response->va_number = $data1->va_number;
          $response->authorization_id = null;
          $response->link = null;
          $response->redirect = $data->transaction->finish_url;
          $response->expiration_date = $data->transaction->date_expired;
          $response->order_id = $data->transaction->id;
        }
       
        return $response;
  }

  public static function status($token)
  {
        $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/status/enc/'. $token;

        $client = new \GuzzleHttp\Client(['http_errors' => false]);
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

            $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/ewallet/create';

            $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
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
          'list_disabled_payment_methods' => null,
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
        $response->authorization_id = $data->payment_link_id;
        $response->link = $data->url;

        return $response;
  }

  
	
	
}
?>