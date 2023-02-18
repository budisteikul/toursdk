<?php
namespace budisteikul\toursdk\Helpers;
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
                $data->bank_name = "paynow";
                $data->bank_code = "";
                $data->bank_country = "SG";
                $data->bank_payment_type = "qrcode";
                //$data->bank_provider = "rapyd";
                $data->bank_provider = "reddotpay";
                $data->bank_payment_method = "sg_paynow_bank";
            break;
            case "poli":
                $data->bank_name = "poli";
                $data->bank_code = "";
                $data->bank_country = "AU";
                $data->bank_payment_method = "au_bank_poli_aud";
                $data->bank_payment_type = "bank_redirect";
                $data->bank_provider = "finmo";
            break;
            case "promptpay":
                $data->bank_name = "promptpay";
                $data->bank_code = "";
                $data->bank_country = "TH";
                $data->bank_payment_type = "qrcode";
                //$data->bank_provider = "rapyd";
                //$data->bank_payment_method = "th_thaipromptpayqr_bank";
                $data->bank_provider = "finmo";
                $data->bank_payment_method = "th_bank_promptpaycash_thb";
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

        $data_json = new \stdClass();
        $status_json = new \stdClass();
        $response_json = new \stdClass();
        
        $data->transaction->mins_expired = 60;
        $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

        $body = [
            'email' => $data->contact->email,
            'country' => $payment->bank_country,
            'ind_bus_type' => 'Individual',
            'first_name' => $data->contact->first_name,
            'last_name' => $data->contact->last_name,
        ];

        $tazapay = self::make_request('POST','/v1/user',$body);
        


        $body = [
            'txn_type' => 'service',
            'release_mechanism' => 'marketplace',
            'initiated_by' => self::env_tazapaySellerID(),
            'buyer_id' => $tazapay['data']['account_id'],
            'seller_id' => self::env_tazapaySellerID(),
            'txn_description' => 'Payment for '. $data->transaction->confirmation_code,
            'invoice_currency' => $data->transaction->currency,
            'invoice_amount' => (float)$data->transaction->amount,
        ];

        $tazapay = self::make_request('POST','/v1/escrow/',$body);
        
        //print_r($body);
        //print_r($tazapay);

        $txn_no = $tazapay['data']['txn_no'];

        $body = [
            'txn_no' => $txn_no,
            'complete_url' => self::env_appUrl() . $data->transaction->finish_url,
            'error_url' => self::env_appUrl() . $data->transaction->finish_url,
            'callback_url' => self::env_appApiUrl() .'/payment/tazapay/confirm'
        ];

        $tazapay = self::make_request('POST','/v1/session/payment',$body);
        
        //print_r($tazapay);
        

        $redirect_url = $tazapay['data']['redirect_url'];
        $redirect_url_array = explode("/",$redirect_url);
        $auth_id = end($redirect_url_array);

        $tazapay = self::make_request('GET','/v1/session/payment/'.$auth_id);
        
        //print_r($tazapay);
        
        $body = [
                'escrow_id' => $tazapay['data']['escrow_id'],
                'payment_method' => $payment->bank_payment_method,
                'redirect' => $redirect_url,
                'provider' => $payment->bank_provider,
                'currency' => $data->transaction->currency,
                'document' => null,
                'is_first_payment' => false,
                'redirect' => self::env_appUrl() . $data->transaction->finish_url
        ];

        $tazapay = self::make_request('POST','/v1/escrow/payment',$body,$tazapay['data']['session_token']);
        
        //print_r($tazapay);
        
        

        if($payment->bank_payment_type=="qrcode")
        {
            $qrcode = $tazapay['data']['qr_code'];
            $data_json->payment_type = 'qrcode';
            $data_json->qrcode = $qrcode;
            $data_json->redirect = $data->transaction->finish_url;
        }

        if($payment->bank_payment_type=="bank_redirect")
        {
            $data_json->payment_type = 'bank_redirect';
            $data_json->redirect = $tazapay['data']['bank_redirect'];
        }
        
        $data_json->bank_name = $payment->bank_name;
        $data_json->bank_code = $payment->bank_code;
            
        $data_json->expiration_date = $data->transaction->date_expired;
        $data_json->order_id = $txn_no;

        $status_json->id = '1';
        $status_json->message = 'success';
        
        $response_json->status = $status_json;
        $response_json->data = $data_json;
        
        return $response_json;
    }

    public static function make_request($method, $path, $body = null, $session_token = null) 
    {
        $base_url = self::tazapayApiEndpoint();
        $access_key = self::env_tazapayAccessKey();     // The access key received from Rapyd.
        $secret_key = self::env_tazapaySecretKey();     // Never transmit the secret key by itself.

        $idempotency = self::generate_string();         // Unique for each request.
        $http_method = $method;                         // Lower case.
        $salt = self::generate_string();                // Randomly generated for each request.
        $date = new \DateTime();
        $timestamp = $date->getTimestamp();             // Current Unix time.

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