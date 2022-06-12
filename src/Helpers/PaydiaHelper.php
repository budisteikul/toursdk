<?php
namespace budisteikul\toursdk\Helpers;

class PaydiaHelper { 
  public static function env_appApiUrl()
  {
        return env("APP_API_URL");
  }

  public static function env_paydiaUseProxy()
  {
        return env("PAYDIA_USE_PROXY",false);
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

  public static function paydiaUseProxy()
  {
      $proxy = null;
      if(self::env_paydiaUseProxy())
      {
            $proxy = 'http://'. self::env_proxyUsername() .':'. self::env_proxyPassword() .'@'. self::env_proxyServer() .':'. self::env_proxyPort();
      }
      return $proxy;
  }

  public static function env_paydiaEnv()
  {
        return env("PAYDIA_ENV");
  }

  public static function env_paydiaClientId()
  {
        return env("PAYDIA_CLIENT_ID");
  }

  public static function env_paydiaSecretKey()
  {
        return env("PAYDIA_SECRET_KEY");
  }

  public static function env_paydiaMid()
  {
        return env("PAYDIA_MID");
  }

  public static function paydiaApiEndpoint()
  {
        if(self::env_paydiaEnv()=="production")
        {
            $endpoint = "https://api.paydia.id";
        }
        else
        {
            $endpoint = "https://api.paydia.co.id";
        }
        return $endpoint;
  }

  public static function createPayment($data)
  {
  		  $url = self::paydiaApiEndpoint();
        $endpoint = $url . '/qris/generate/';

        $signature = base64_encode(self::env_paydiaClientId().':'.self::env_paydiaSecretKey().':'.self::env_paydiaMid());

        $headers = [
              'Accept' => '*/*',
              'Content-Type' => 'application/json',
              'Authorization' => 'Bearer '. $signature
          ];

        $transaction_id = $data->transaction->id;
        $transaction_id = str_replace("PAY-", "", $transaction_id);
        $transaction_id = str_replace("VER-", "", $transaction_id);

       
        
        $data_json = [
        	'merchantid' => self::env_paydiaMid(),
        	'nominal' => (int)$data->transaction->amount,
        	'tip' => 0,
        	'ref' => $transaction_id,
        	'callback' => self::env_appApiUrl().'/payment/paydia/confirm',
        	'expire' => $data->transaction->mins_expired
        ];
        

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          [
            'json' => $data_json,
            'proxy' => self::paydiaUseProxy()
          ]
        );

    $data1 = $response->getBody()->getContents();
    $data1 = json_decode($data1);

   
    $response = new \stdClass();
    $response->bank_name = 'paydia';
		$response->qrcode = $data1->rawqr;
		$response->link = null;
		$response->expiration_date = $data->transaction->date_expired;
		$response->authorization_id = $signature;
		$response->order_id = $data1->refid;
		$response->payment_type = 'qrcode';
		$response->redirect = $data->transaction->finish_url;

		return $response;
  }

  public static function checkSignature($request)
  {
     $status = false;
     $data = $request->all();
     $signature = null;
     if(isset($data['signature'])) $signature = $data['signature'];
     if($signature==base64_encode(self::env_paydiaClientId().':'.self::env_paydiaSecretKey().':'.self::env_paydiaMid()))
     {
        $status = true;
     }
     return $status;
  }

}
?>