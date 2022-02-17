<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Models\Disbursement;
use Carbon\Carbon;

class DokuHelper {

	public static function env_appName()
  	{
        return env("APP_NAME");
  	}

	public static function env_dokuEnv()
  	{
        return env("DOKU_ENV");
  	}

  	public static function env_dokuClientId()
  	{
        return env("DOKU_CLIENT_ID");
  	}

  	public static function env_dokuSecretKey()
  	{
        return env("DOKU_SECRET_KEY");
  	}

  	public static function dokuApiEndpoint()
  	{
        if(self::env_dokuEnv()=="production")
        {
            $endpoint = "https://api.doku.com";
        }
        else
        {
            $endpoint = "https://api-sandbox.doku.com";
        }
        return $endpoint;
  	}

  	public static function createVA($shoppingcart,$bank="")
  	{
  		$first_name = BookingHelper::get_answer($shoppingcart,'firstName');
        $last_name = BookingHelper::get_answer($shoppingcart,'lastName');
        $email = BookingHelper::get_answer($shoppingcart,'email');
        $phone = BookingHelper::get_answer($shoppingcart,'phoneNumber');

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
        if($mins<=60) $mins = 60;

  		if($bank=="doku")
  		{
  			$data = [
          		'order' => [
          			'invoice_number' => $shoppingcart->confirmation_code,
          			'amount' => $shoppingcart->total
          			],
          		'virtual_account_info' => [
          			'billing_type' => 'FIX_BILL',
          			'expired_time' => $mins,
          			'reusable_status' => false,
          			'info1' => self::env_appName()
          		],
          		'customer' => [
          			'name' => $first_name .' '. $last_name,
          			'email' => $email
          		]
        	];
        	$targetPath = '/doku-virtual-account/v2/payment-code';
  		}
  		else if ($bank=="permata")
  		{
            $ref_info[] = array(
                'ref_name' => $first_name .' '. $last_name,
                'ref_value' => self::env_appName()
            );

  			$data = [
          		'order' => [
          			'invoice_number' => $shoppingcart->confirmation_code,
          			'amount' => $shoppingcart->total
          			],
          		'virtual_account_info' => [
          			'billing_type' => 'FIX_BILL',
          			'expired_time' => $mins,
          			'reusable_status' => false,
          			'ref_info' => $ref_info
          		],
          		'customer' => [
          			'name' => $first_name .' '. $last_name,
          			'email' => $email
          		]
        	];
        	$targetPath = '/permata-virtual-account/v2/payment-code';
  		}
  		else
  		{
  			return "";
  		}

        //print_r($data);
        //exit();
  		$url = self::dokuApiEndpoint();
        $endpoint = $url . $targetPath;

  		$requestId = rand(1, 100000);

  		$dateTime = gmdate("Y-m-d H:i:s");
        $dateTime = date(DATE_ISO8601, strtotime($dateTime));
        $dateTimeFinal = substr($dateTime, 0, 19) . "Z";

        

        //create signature
        $header = array();
        $header['Client-Id'] = self::env_dokuClientId();
        $header['Request-Id'] = $requestId;
        $header['Request-Timestamp'] = $dateTimeFinal;
        $signature = self::generateSignature($header, $targetPath, json_encode($data), self::env_dokuSecretKey());

  		$headers = [
              'Content-Type' => 'application/json',
              'Signature' => $signature,
              'Request-Id' => $requestId,
              'Client-Id' => self::env_dokuClientId(),
              'Request-Timestamp' => $dateTimeFinal,
              'Request-Target' => $targetPath,
          ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        
        //print_r($data);
        //exit();
        return $data;
  	}

	public static function generateSignature($headers, $targetPath, $body, $secret)
    {
        $digest = base64_encode(hash('sha256', $body, true));
        $rawSignature = "Client-Id:" . $headers['Client-Id'] . "\n"
            . "Request-Id:" . $headers['Request-Id'] . "\n"
            . "Request-Timestamp:" . $headers['Request-Timestamp'] . "\n"
            . "Request-Target:" . $targetPath . "\n"
            . "Digest:" . $digest;

        $signature = base64_encode(hash_hmac('sha256', $rawSignature, $secret, true));
        return 'HMACSHA256=' . $signature;
    }

}