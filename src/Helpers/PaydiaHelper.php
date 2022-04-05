<?php
namespace budisteikul\toursdk\Helpers;


class PaydiaHelper { 
  public static function env_appApiUrl()
  {
        return env("APP_API_URL",false);
  }

  public static function env_paydiaUseProxy()
  {
        return env("PAYDIA_USE_PROXY",false);
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

  public static function paydiaUseProxy()
  {
      $proxy = null;
      if(self::env_paydiaUseProxy())
      {
            $proxy = 'http://'. self::env_proxyUsermame() .':'. self::env_proxyPassword() .'@'. self::env_proxyServer() .':'. self::env_proxyPort();
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

        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'Authorization' => 'Bearer '. base64_encode(self::env_paydiaClientId().':'.self::env_paydiaSecretKey().':'.self::env_paydiaMid())
          ];

        $data_json = [
        	'merchantid' => self::env_paydiaMid(),
        	'nominal' => $data->transaction->amount,
        	'tip' => 0,
        	'ref' => $data->transaction->id,
        	'callback' => self::env_appApiUrl().'/payment/paydia/confirm',
        	//'expire' => $data->transaction->mins_expired
        	'expire' => 5
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

        print_r($data1);
        exit();

        $response = new \stdClass();
        $response->bank_name = 'paydia';
		$response->qrcode = $data1->rawqr;
		$response->link = null;
		//$response->expiration_date = $data->transaction->date_expired;
		$response->expiration_date = 5;
		$response->order_id = $data->transaction->id;
		$response->payment_type = 'qris';
		$response->redirect = $data->transaction->finish_url;

		return $response;
  }

}
?>