<?php
namespace budisteikul\toursdk\Helpers;

class RapydHelper {

    public static function env_rapydEnv()
    {
        return env("RAPYD_ENV");
    }

	  public static function env_rapydAccessKey()
  	{
        return env("RAPYD_ACCESS_KEY");
  	}

  	public static function env_rapydSecretKey()
  	{
        return env("RAPYD_SECRET_KEY");
  	}

  	public static function generate_string($length=12)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($permitted_chars), 0, $length);
    }

    public static function rapydApiEndpoint()
    {
        if(self::env_rapydEnv()=="production")
        {
            $endpoint = "https://api.rapyd.net";
        }
        else
        {
            $endpoint = "https://sandboxapi.rapyd.net";
        }
        return $endpoint;
    }

    public static function make_request($method, $path, $body = null) {
        $base_url = self::rapydApiEndpoint();
        $access_key = self::env_rapydAccessKey();     // The access key received from Rapyd.
        $secret_key = self::env_rapydSecretKey();     // Never transmit the secret key by itself.

        $idempotency = self::generate_string();       // Unique for each request.
        $http_method = $method;                       // Lower case.
        $salt = self::generate_string();              // Randomly generated for each request.
        $date = new DateTime();
        $timestamp = $date->getTimestamp();           // Current Unix time.

        $body_string = !is_null($body) ? json_encode($body,JSON_UNESCAPED_SLASHES) : '';
        $sig_string = "$http_method$path$salt$timestamp$access_key$secret_key$body_string";

        $hash_sig_string = hash_hmac("sha256", $sig_string, $secret_key);
        $signature = base64_encode($hash_sig_string);

        $request_data = NULL;

        if ($method === 'post') {
            $request_data = array(
                CURLOPT_URL => "$base_url$path",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body_string
            );
        } else {
            $request_data = array(
                CURLOPT_URL => "$base_url$path",
                CURLOPT_RETURNTRANSFER => true,
            );
        }

        $curl = curl_init();
        curl_setopt_array($curl, $request_data);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "access_key: $access_key",
            "salt: $salt",
            "timestamp: $timestamp",
            "signature: $signature",
            "idempotency: $idempotency"
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception("cURL Error #:".$err);
        } else {
            return json_decode($response, true); 
        }
    }
}