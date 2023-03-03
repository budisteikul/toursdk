<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\GeneralHelper;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class DuitkuHelper {

  public static function env_appUrl()
    {
        return env("APP_URL");
    }

	public static function env_appApiUrl()
  	{
        return env("APP_API_URL");
  	}

    public static function env_appName()
    {
        return env("APP_NAME");
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

  	public static function duitkuSnapApiEndpoint()
  	{
        if(self::env_duitkuEnv()=="production")
        {
            $endpoint = "https://api-prod.duitku.com";
        }
        else
        {
            $endpoint = "https://api-sandbox.duitku.com";
        }
        return $endpoint;
  	}

    public static function duitkuChargeApiEndpoint()
    {
        if(self::env_duitkuEnv()=="production")
        {
            $endpoint = "https://app-prod.duitku.com";
        }
        else
        {
            $endpoint = "https://app-sandbox.duitku.com";
        }
        return $endpoint;
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

  	public static function payment($bank)
    {
        $data = new \stdClass();
        switch($bank)
        {
            case "linkaja":
                $data->bank_name = "linkaja";
                $data->bank_code = "";
                $data->bank_payment_type = "LA";
            break;
            case "linkaja_qris":
                $data->bank_name = "qris";
                $data->bank_code = "";
                $data->bank_payment_type = "LQ";
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

        $payment = self::payment($data->transaction->bank);

        $data->transaction->amount = (int)$data->transaction->amount;
        $data->transaction->mins_expired = 60;
        $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

        
        if($payment->bank_payment_type=="LA")
        {
            $data1 = self::createSnap($data);
            $data2 = self::getStatus($data1->paymentUrl);
		    $ticket = GeneralHelper::get_string_between($data2,'"ticket":"','"');
            $data3 = self::createCharge($data1->reference,$payment,$ticket);
            
            $data_json->payment_type = 'ewallet';
            $data_json->redirect = $data3->qrString;
        }
        else if($payment->bank_payment_type=="LQ")
        {   

            $data1 = self::createTransaction($data,$payment);
            
            $data_json->bank_name = $payment->bank_name;
            $data_json->qrcode = $data1->qrString;
            
            $data_json->payment_type = 'qrcode';
            $data_json->redirect = $data->transaction->finish_url;
            
        }
		
        $data_json->authorization_id = $data1->reference;
        $data_json->bank_name = $payment->bank_name;
        $data_json->bank_code = $payment->bank_code;
        $data_json->expiration_date = $data->transaction->date_expired;
        $data_json->order_id = $data->transaction->id;
        
        $status_json->id = '1';
        $status_json->message = 'success';
        
        $response_json->status = $status_json;
        $response_json->data = $data_json;
        
        return $response_json;
    }

    public static function getStatus($url)
    { 
        $headers = [
                'content-type' => 'application/json',
            ];
        $client = new \GuzzleHttp\Client(['headers' => $headers]);

        $response = $client->request('GET', $url);
        $contents = $response->getBody()->getContents();
        
        return $contents;
    }

    public static function createCharge($token,$payment,$ticket)
    {
        $data = [
            'channel' => $payment->bank_payment_type,
        ];
        
        $timestamp = round(microtime(true) * 1000);
        $url = self::duitkuChargeApiEndpoint();

        $headers = [
              'Content-Type' => 'application/json',
              'Content-Length' => strlen(json_encode($data)),
              'x-duitku-timestamp' => $timestamp,
              'x-duitku-ticket' => $ticket,
          ];
        
        $targetPath = $url . '/api/process/'. $token;
        $endpoint = $targetPath;

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);

        return $data;
    }

    public static function createSnap($data)
    {
    	$merchantCode = self::env_duitkuMerchantCode();
    	$apiKey = self::env_duitkuApiKey();
    	$paymentAmount = $data->transaction->amount;
    	$merchantOrderId = $data->transaction->id;
    	$productDetails = 'Payment for '. self::env_appName();
    	$email = $data->contact->email;
    	$customerVaName = $data->contact->name;
    	$callbackUrl = self::env_appApiUrl().'/payment/duitku/confirm';
    	$returnUrl = self::env_appUrl() . $data->transaction->finish_url;
    	$expiryPeriod = $data->transaction->mins_expired;
        $timestamp = round(microtime(true) * 1000);
        $phoneNumber = $data->contact->phone;
        $signature = hash("sha256", $merchantCode . $timestamp . $apiKey);

        $item1 = array(
            'name' => $data->transaction->confirmation_code,
            'price' => $paymentAmount,
            'quantity' => 1);

        $itemDetails = array(
            $item1
        );

    	$data = [
            'merchantCode' => $merchantCode,
            'apiKey' => $apiKey,
            'paymentAmount' => $paymentAmount,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'email' => $email,
            'customerVaName' => $customerVaName,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'expiryPeriod' => $expiryPeriod,
            'email' => $data->contact->email,
            'itemDetails' => $itemDetails,
        ];

        $headers = [
              'Content-Type' => 'application/json',
              'Content-Length' => strlen(json_encode($data)),
              'x-duitku-signature' => $signature,
              'x-duitku-timestamp' => $timestamp,
              'x-duitku-merchantcode' => self::env_duitkuMerchantCode(),
          ];

        $url = self::duitkuSnapApiEndpoint();
        $targetPath = '/api/merchant/createInvoice';
        $endpoint = $url . $targetPath;

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        return $data;
    }

    public function createOvoTransaction($paymentAmount,$phoneNumber)
    {
        $merchantCode = self::env_duitkuMerchantCode();
        $merchantOrderId = Uuid::uuid4()->toString();
        $paymentAmount = (int)$paymentAmount;
        $apiKey = self::env_duitkuApiKey();
        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

        $data = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $paymentAmount,
            'merchantOrderId' =>  $merchantOrderId,
            'productDetails' => env('APP_NAME'),
            'email' => env('MAIL_FROM_ADDRESS'),
            'phoneNumber' => $phoneNumber,
            'signature' => $signature
        ];

        $headers = [
              'Content-Type' => 'application/json',
              'Content-Length' => strlen(json_encode($data)),
          ];

        $url = self::duitkuApiEndpoint();
        $targetPath = '/webapi/api/merchant/ovo/createtransaction';
        $endpoint = $url . $targetPath;

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        return $data;
    }

    public static function createTransaction($data,$payment)
    {
        $merchantCode = self::env_duitkuMerchantCode();
        $apiKey = self::env_duitkuApiKey();
        $paymentAmount = $data->transaction->amount;
        $paymentMethod = $payment->bank_payment_type;
        $merchantOrderId = $data->transaction->id;
        $productDetails = 'Payment for '. self::env_appName();
        $email = $data->contact->email;
        $customerVaName = $data->contact->name; 
        $callbackUrl = self::env_appApiUrl().'/payment/duitku/confirm';
        $returnUrl = self::env_appUrl() . $data->transaction->finish_url;
        $expiryPeriod = $data->transaction->mins_expired;
        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $apiKey);

        $item1 = array(
            'name' => $data->transaction->confirmation_code,
            'price' => $paymentAmount,
            'quantity' => 1);

        $itemDetails = array(
            $item1
        );

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
            'itemDetails' => $itemDetails,
        ];

        $headers = [
              'Content-Type' => 'application/json',
              'Content-Length' => strlen(json_encode($data)),
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
