<?php
namespace budisteikul\toursdk\Helpers;
use Carbon\Carbon;

class DuitkuHelper {

	public static function env_appApiUrl()
  	{
        return env("APP_API_URL");
  	}

	public static function env_duitkuEnv()
  	{
        return env("DUITKU_ENV");
  	}

	public static function env_duitkuMerchantCode()
  	{
        return env("DUITKU_MERCHANT_CODE");
  	}

	public static function env_duitkuApiKey()
  	{
        return env("DUITKU_API_KEY");
  	}

  	public static function duitkuApiEndpoint()
  	{
        if(self::env_duitkuEnv()=="production")
        {
            $endpoint = "https://passport.duitku.com";
        }
        else
        {
            $endpoint = "https://sandbox.duitku.com";
        }
        return $endpoint;
  	}

  	public static function bankCode($bank)
    {
        $data = new \stdClass();
        switch($bank)
        {
            case "sampoerna":
                $data->bank_name = "sahabat sampoerna";
                $data->bank_code = "523";
                $data->bank_payment_type = "S1";
            break;
            case "ovo":
                $data->bank_name = "ovo";
                $data->bank_code = "";
                $data->bank_payment_type = "OV";
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

        $data->transaction->mins_expired = 60;
		$data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

		$data1 = self::createSnap($data,$payment);

		$response->payment_type = 'bank_transfer';
		$response->va_number = $data1->vaNumber;
		$response->link = $data1->paymentUrl;

		$response->authorization_id = $data1->reference;
        $response->bank_name = $payment->bank_name;
        $response->bank_code = $payment->bank_code;
        $response->redirect = $data->transaction->finish_url;
        $response->expiration_date = $data->transaction->date_expired;
        $response->order_id = $data->transaction->id;
        
        return $response;
    }

    public static function createSnap($data,$payment)
    {
    	$merchantCode = self::env_duitkuMerchantCode(); // dari duitku
    	$apiKey = self::env_duitkuApiKey(); // dari duitku
    	$paymentAmount = $data->transaction->amount;
    	$paymentMethod = $payment->bank_payment_type; // VC = Credit Card
    	$merchantOrderId = $data->transaction->id; // dari merchant, unik
    	$productDetails = 'Payment for '. $data->transaction->confirmation_code;
    	$email = $data->contact->email; // email pelanggan anda
    	$customerVaName = $data->contact->name; // tampilan nama pada tampilan konfirmasi bank
    	$callbackUrl = self::env_appApiUrl().'/payment/duitku/confirm'; // url untuk callback
    	$returnUrl = $data->transaction->finish_url; // url untuk redirect
    	$expiryPeriod = $data->transaction->mins_expired; // atur waktu kadaluarsa dalam hitungan menit
    	$signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

    	$data = [
            'merchantCode' => $merchantCode,
            'apiKey' => $apiKey,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'email' => $email,
            'customerVaName' => $customerVaName,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'expiryPeriod' => $expiryPeriod,
            'signature' => $signature,
        ];

        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
          ];

        $url = self::duitkuApiEndpoint();
        $targetPath = '/webapi/api/merchant/v2/inquiry';
        $endpoint = $url . $targetPath;

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);

        return $data;
    }

    public static function checkSignature($request)
    {
    	$status = false;
      	$data = $request->all();
      	$signature = null;
      	$amount = null;
      	$merchantOrderId = null;
      
      	if(isset($data['signature'])) $signature = $data['signature'];
      	if(isset($data['amount'])) $amount = $data['amount'];
      	if(isset($data['merchantOrderId'])) $merchantOrderId = $data['merchantOrderId'];

      	$params = self::env_duitkuMerchantCode() . $amount . $merchantOrderId . self::env_duitkuApiKey();
      	$calcSignature = md5($params);

      	if($signature == $calcSignature)
      	{
      		$status = true;
      	}
      	
      	return $status;
    }

}
?>