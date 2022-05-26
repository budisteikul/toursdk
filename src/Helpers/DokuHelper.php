<?php
namespace budisteikul\toursdk\Helpers;
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

    public static function bankCode($bank)
    {
        $data = new \stdClass();
        switch($bank)
        {
            case "doku":
                $data->bank_name = "doku";
                $data->bank_code = "899";
                $data->bank_payment_type = "doku";
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
            case "danamon":
                $data->bank_name = "danamon";
                $data->bank_code = "011";
                $data->bank_payment_type = "danamon";
            break;
            case "mandirisyariah":
                $data->bank_name = "bsi";
                $data->bank_code = "451-900";
                $data->bank_payment_type = "mandirisyariah";
            break;
            case "bsi":
                $data->bank_name = "bsi";
                $data->bank_code = "451-900";
                $data->bank_payment_type = "mandirisyariah";
            break;
            case "qris":
                $data->bank_name = "doku";
                $data->bank_code = "";
                $data->bank_payment_type = "qris_doku";
            break;
            case "ovo":
                $data->bank_name = "ovo";
                $data->bank_code = "";
                $data->bank_payment_type = "ovo";
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

        if($payment->bank_payment_type=="qris_doku")
        {
            $data->transaction->mins_expired = 60;
            $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

            $data1 = self::createSnap($data);
            $data2 = self::createCharge($data1->response->payment->token_id,$data,$payment);

            $response->payment_type = 'qris';
            $response->qrcode = $data2->qr_code;
        }
        else if($payment->bank_payment_type=="ovo")
        {
            $data->transaction->mins_expired = 60;
            $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

            $data1 = self::createSnap($data);
            $data2 = self::createCharge($data1->response->payment->token_id,$data,$payment);

            print_r($data1);
            print_r($data2);
            exit();
        }
        else
        {
            $data1 = self::createSnap($data);
            $data2 = self::createCharge($data1->response->payment->token_id,$data,$payment);

            $response->payment_type = 'bank_transfer';
            $response->va_number = $data2->payment_code;
            $response->link = $data2->how_to_pay_url;
        }

        $response->authorization_id = $data1->response->payment->token_id;
        $response->bank_name = $payment->bank_name;
        $response->bank_code = $payment->bank_code;
        $response->redirect = $data->transaction->finish_url;
        $response->expiration_date = $data->transaction->date_expired;
        $response->order_id = $data->transaction->id;
        
        return $response;
    }



    public static function createCharge($token,$data,$payment)
    {
        if($payment->bank_payment_type=="qris_doku")
        {
            $data = [
                'token_id' => $token
            ];
            $targetPath = '/checkout/v1/payment/'.$token.'/generate-qris';
        }
        else if($payment->bank_payment_type=="ovo")
        {
            $data = [
                'token_id' => $token,
                'customer_phone' => $data->contact->phone
            ];
            $targetPath = '/checkout/v1/payment/'.$token.'/emoney-ovo';
        }
        else
        {
            $data = [
                'token_id' => $token,
                'lang' => 'en',
                'bank' => $payment->bank_payment_type
            ];
            $targetPath = '/checkout/v1/payment/'.$token.'/generate-code';
        }
        

        $url = self::dokuApiEndpoint();
        $endpoint = $url . $targetPath;

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

        return $data;
    }

    public static function createSnap($data)
    {

        $data = [
            'order' => [
                'invoice_number' => $data->transaction->id,
                'amount' => $data->transaction->amount
             ],
             'payment' => [
                'payment_due_date' => $data->transaction->mins_expired
             ],
             'customer' => [
                'id' => $data->transaction->id,
                'name' => $data->contact->name,
                'email' => $data->contact->email,
                //'phone' => $data->contact->phone
             ]
        ];

        $targetPath = '/checkout/v1/payment';

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
              'Signature' => $signature,
              'Request-Id' => $requestId,
              'Client-Id' => self::env_dokuClientId(),
              'Request-Timestamp' => $dateTimeFinal
          ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);

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

    public static function checkSignature($request)
    {
        $status = false;
        $notificationBody = $request->getContent();
        $notificationPath = '/'.$request->path();
        $secretKey = self::env_dokuSecretKey();

        $digest = base64_encode(hash('sha256', $notificationBody, true));
        $rawSignature = "Client-Id:" . $request->header('Client-Id') . "\n"
        . "Request-Id:" . $request->header('Request-Id') . "\n"
        . "Request-Timestamp:" . $request->header('Request-Timestamp') . "\n"
        . "Request-Target:" . $notificationPath . "\n"
        . "Digest:" . $digest;

        $signature = base64_encode(hash_hmac('sha256', $rawSignature, $secretKey, true));
        $finalSignature = 'HMACSHA256=' . $signature;

        if($request->header('Signature')==$finalSignature)
        {
            $status = true;
        }

        return $status;
    }

}