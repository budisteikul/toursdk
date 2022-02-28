<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\ImageHelper;
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
      break;
      case "bri":
        $data->bank_name = "bri";
        $data->bank_code = "002";
      break;
      case "cimb":
        $data->bank_name = "cimb niaga";
        $data->bank_code = "022";
      break;
      case "mandiri":
        $data->bank_name = "mandiri";
        $data->bank_code = "008";
      break;
      case "permata":
        $data->bank_name = "permata";
        $data->bank_code = "013";
      break;
      case "bni":
        $data->bank_name = "bni";
        $data->bank_code = "009";
      break;
    }
    return $data;
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
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        

        

        return $data;
       
  }

  public static function createPayment($data)
  {
        $response = new \stdClass();

        if($data->transaction->bank=="qris")
        {
          $data1 = self::createSnap($data);
          $data2 = self::createCharge($data,$data1->snaptoken,$data->transaction->bank);
          $qrcode = ImageHelper::uploadQrcodeCloudinary($data2->data->qris_url);
          $response->payment_type = 'ewallet';
          $response->bank_name = 'shopeepay';
          $response->qrcode = $qrcode['secure_url'];
          $response->link = self::oyLink($data1->snaptoken);
        }
        else
        {
          $data1 = self::createSnap($data);
          
          $data2 = self::createCharge($data,$data1->snaptoken,$data->transaction->bank);
          
          $data3 = self::status($data1->snaptoken);
          
          $response->payment_type = 'bank_transfer';
          $response->bank_name = self::bankCode($data->transaction->bank)->bank_name;
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
        $response = $client->request('GET',$endpoint);

        $data = $response->getBody()->getContents();

      

        $data = json_decode($data);

        

        return $data;
  }

  public static function createCharge($data,$token,$bank)
  {
        if($bank=="qris")
        {
            $data = [
                'tx_id' => $token,
                'amount' => $data->transaction->amount,
                'sender_name' => $data->contact->name,
                'sender_phone' => NULL,
                'sender_notes' => NULL,
                'sender_email' => NULL,
                'email_active' => false,
                'card_sender' => 'qris_shopee'
            ];

            $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/qris/create';
            $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json'
          ];

          $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
          $response = $client->request('POST',$endpoint,
              ['json' => $data]
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
                'card_sender' => self::bankCode($bank)->bank_code,
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
              ['json' => $data]
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
          ['json' => $data]
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