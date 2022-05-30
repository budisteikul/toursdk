<?php
namespace budisteikul\toursdk\Helpers;
use Zxing\QrReader;
use Storage;
use Carbon\Carbon;

class TazapayHelper {

    public static function env_appUrl()
    {
        return env("APP_URL");
    }

    public static function env_appApiUrl()
    {
        return env("APP_API_URL");
    }

    public static function env_tazapayEnv()
    {
        return env("TAZAPAY_ENV");
    }

	  public static function env_tazapayAccessKey()
  	{
        return env("TAZAPAY_ACCESS_KEY");
  	}

  	public static function env_tazapaySecretKey()
  	{
        return env("TAZAPAY_SECRET_KEY");
  	}

    public static function env_tazapaySellerID()
    {
        return env("TAZAPAY_SELLER_ID");
    }

  	public static function generate_string($length=12)
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($permitted_chars), 0, $length);
    }

    public static function tazapayApiEndpoint()
    {
        if(self::env_tazapayEnv()=="production")
        {
            $endpoint = "https://api.tazapay.com";
        }
        else
        {
            $endpoint = "https://api-sandbox.tazapay.com";
        }
        return $endpoint;
    }

    public static function bankCode($bank)
    {
        $data = new \stdClass();
        switch($bank)
        {
            case "paynow":
                $data->bank_name = "dbs";
                $data->bank_code = "7171";
                $data->bank_payment_type = "sg_paynow_bank";
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

        $data->transaction->mins_expired = 3;
        $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

        $body = [
            'email' => $data->contact->email,
            'country' => 'SG',
            'ind_bus_type' => 'Individual',
            'first_name' => $data->contact->first_name,
            'last_name' => $data->contact->last_name,
        ];

        $tazapay = self::make_request('POST','/v1/user',$body);
        print_r($tazapay);

        $body = [
            'txn_type' => 'service',
            'release_mechanism' => 'marketplace',
            'initiated_by' => self::env_tazapaySellerID(),
            'buyer_id' => $tazapay['data']['account_id'],
            'seller_id' => self::env_tazapaySellerID(),
            'txn_description' => 'Payment for '. $data->transaction->confirmation_code,
            'invoice_currency' => 'SGD',
            'invoice_amount' => $data->transaction->amount,
        ];

        $tazapay = self::make_request('POST','/v1/escrow/',$body);
        print_r($tazapay);
        $txn_no = $tazapay['data']['txn_no'];

        $body = [
            'txn_no' => $txn_no,
            'complete_url' => self::env_appUrl() . $data->transaction->finish_url,
            'error_url' => self::env_appUrl() . $data->transaction->finish_url,
            'callback_url' => self::env_appApiUrl() .'/payment/tazapay/confirm'
        ];

        $tazapay = self::make_request('POST','/v1/session/payment',$body);
        print_r($tazapay);

        $redirect_url = $tazapay['data']['redirect_url'];
        $redirect_url_array = explode("/",$redirect_url);
        $auth_id = end($redirect_url_array);

        $tazapay = self::make_request('GET','/v1/session/payment/'.$auth_id);
        print_r($tazapay);
            
        $body = [
                'escrow_id' => $tazapay['data']['escrow_id'],
                'payment_method' => 'sg_paynow_bank',
                'redirect' => $redirect_url,
                'provider' => 'rapyd',
                'currency' => 'SGD',
                'document' => null,
                'is_first_payment' => null,
        ];

        $tazapay = self::make_request('POST','/v1/escrow/payment',$body,$tazapay['data']['session_token']);
        print_r($tazapay);

            $qrcode = $tazapay['data']['qr_code'];
            list($type, $qrcode) = explode(';', $qrcode);
            list(, $qrcode)      = explode(',', $qrcode);
            $contents = base64_decode($qrcode);

            $path = date('YmdHis');
            $disk = Storage::disk('gcs');
            $disk->put('qrcode/'. $path .'/'.$data->transaction->confirmation_code.'.png', $contents);
            $url = $disk->url('qrcode/'. $path .'/'.$data->transaction->confirmation_code.'.png');
            $qrcode = new QrReader($url);

            $response->payment_type = 'paynow';
            $response->qrcode = $qrcode->text();

            //$response->authorization_id = $data1['data']['id'];
            $response->bank_name = $payment->bank_name;
            $response->bank_code = $payment->bank_code;
            $response->redirect = $data->transaction->finish_url;
            $response->expiration_date = $data->transaction->date_expired;
            $response->order_id = $txn_no;
        
            return $response;
    }

    public static function make_request($method, $path, $body = null, $session_token = null) 
    {
        $base_url = self::tazapayApiEndpoint();
        $access_key = self::env_tazapayAccessKey();     // The access key received from Rapyd.
        $secret_key = self::env_tazapaySecretKey();     // Never transmit the secret key by itself.

        $idempotency = self::generate_string();      // Unique for each request.
        $http_method = $method;                // Lower case.
        $salt = self::generate_string();             // Randomly generated for each request.
        $date = new \DateTime();
        $timestamp = $date->getTimestamp();    // Current Unix time.

        $body_string = !is_null($body) ? json_encode($body,JSON_UNESCAPED_SLASHES) : '';
        $sig_string = "$http_method$path$salt$timestamp$access_key$secret_key";

        $hash_sig_string = hash_hmac("sha256", $sig_string, $secret_key);
        $signature = base64_encode($hash_sig_string);

        $request_data = NULL;

        if ($method === 'POST') {
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

        if($session_token!=null)
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "accesskey: $access_key",
                "salt: $salt",
                "timestamp: $timestamp",
                "signature: $signature",
                "X-Session-Token: $session_token"
            ));
        }
        else
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "accesskey: $access_key",
                "salt: $salt",
                "timestamp: $timestamp",
                "signature: $signature",
            ));
        }
    

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