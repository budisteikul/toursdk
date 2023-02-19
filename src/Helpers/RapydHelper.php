<?php
namespace budisteikul\toursdk\Helpers;
use Ramsey\Uuid\Uuid;

class RapydHelper {

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

    public static function bankCode($bank)
    {
        $data = new \stdClass();
        switch($bank)
        {
            case "fast":
                $data->bank_name = "dbs";
                $data->bank_code = "7171";
                $data->bank_payment_type = "sg_fast_bank";
            break;
            case "paynow":
                $data->bank_name = "paynow";
                $data->bank_code = "";
                $data->bank_payment_type = "sg_paynow_bank";
            break;
            case "poli":
                $data->bank_name = "poli";
                $data->bank_code = "";
                $data->bank_payment_type = "au_poli_bank";
            break;
            case "qris":
                $data->bank_name = "qris";
                $data->bank_code = "";
                $data->bank_payment_type = "id_qris_bank";
            break;
            case "bri":
                $data->bank_name = "bri";
                $data->bank_code = "002";
                $data->bank_payment_type = "id_bri_bank";
            break;
            case "permata":
                $data->bank_name = "permata";
                $data->bank_code = "013";
                $data->bank_payment_type = "id_permata_bank";
            break;
            case "mandiri":
                $data->bank_name = "mandiri";
                $data->bank_code = "013";
                $data->bank_payment_type = "id_mandiri_bank";
            break;
            case "cimb":
                $data->bank_name = "cimb";
                $data->bank_code = "022";
                $data->bank_payment_type = "id_cimb_bank";
            break;
            case "bni":
                $data->bank_name = "bni";
                $data->bank_code = "009";
                $data->bank_payment_type = "id_bni_bank";
            break;
            case "sinarmas":
                $data->bank_name = "sinarmas";
                $data->bank_code = "153";
                $data->bank_payment_type = "id_sinarmas_bank";
            break;
            case "maybank":
                $data->bank_name = "maybank";
                $data->bank_code = "016";
                $data->bank_payment_type = "id_maybank_bank";
            break;
            case "bca":
                $data->bank_name = "bca";
                $data->bank_code = "014";
                $data->bank_payment_type = "id_bca_bank";
            break;
            case "danamon":
                $data->bank_name = "danamon";
                $data->bank_code = "011";
                $data->bank_payment_type = "id_danamon_bank";
            break;
            case "grabpay":
                $data->bank_name = "grabpay";
                $data->bank_code = "";
                $data->bank_payment_type = "ph_grabpay_ewallet";
            break;
            case "gcash":
                $data->bank_name = "gcash";
                $data->bank_code = "";
                $data->bank_payment_type = "ph_gcash_ewallet";
            break;
            case "tmoney":
                $data->bank_name = "tmoney";
                $data->bank_code = "";
                $data->bank_payment_type = "kr_tmoney_ewallet";
            break;
            case "alfamart":
                $data->bank_name = "alfamart";
                $data->bank_code = "";
                $data->bank_payment_type = "id_alfa_cash";
            break;
            case "bancnet":
                $data->bank_name = "bancnet";
                $data->bank_code = "";
                $data->bank_payment_type = "ph_bancnet_bank";
            break;
            case "promptpay":
                $data->bank_name = "promptpay";
                $data->bank_code = "";
                $data->bank_payment_type = "th_thaipromptpayqr_bank";
            break;
            case "creditcard":
                $data->bank_name = "rapyd";
                $data->bank_code = "";
                $data->bank_payment_type = "";
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

        
        if($data->transaction->bank=="paynow")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);
            $qrcode = $data1['data']['visual_codes']['PayNow QR'];
            
            $data_json->payment_type = 'qrcode';
            $data_json->qrcode = $qrcode;
            $data_json->redirect = $data->transaction->finish_url;
        }
        else if($data->transaction->bank=="creditcard")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'country' => 'ID',
                'currency' => 'IDR',
                'requested_currency' => 'IDR',
                'complete_checkout_url' => self::env_appUrl() . $data->transaction->finish_url,
                'cancel_checkout_url' => self::env_appUrl() . $data->transaction->finish_url,
                'merchant_reference_id' => Uuid::uuid4()->toString(),
            ];

            $data1 = self::make_request('post','/v1/checkout',$body);
            $data_json->payment_type = 'bank_redirect';
            $data_json->redirect = $data1['data']['redirect_url'];

        }
        else if($data->transaction->bank=="poli")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'complete_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'error_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'description' => self::env_appName(),
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);
            

            $data_json->payment_type = 'bank_redirect';
            $data_json->redirect = $data1['data']['redirect_url'];
        }
        else if($data->transaction->bank=="bancnet")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'complete_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'error_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'description' => self::env_appName(),
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);
            

            $data_json->payment_type = 'bank_redirect';
            $data_json->redirect = $data1['data']['redirect_url'];
        }
        else if($data->transaction->bank=="grabpay")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'complete_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'error_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'description' => self::env_appName(),
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);
            

            $data_json->payment_type = 'ewallet';
            $data_json->redirect = $data1['data']['redirect_url'];
        }
        else if($data->transaction->bank=="gcash")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'complete_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'error_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'description' => self::env_appName(),
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);
            

            $data_json->payment_type = 'ewallet';
            $data_json->redirect = $data1['data']['redirect_url'];
        }
        else if($data->transaction->bank=="tmoney")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'complete_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'error_payment_url' => self::env_appUrl() . $data->transaction->finish_url,
                'description' => self::env_appName(),
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);
            

            $data_json->payment_type = 'bank_redirect';
            $data_json->redirect = $data1['data']['redirect_url'];
        }
        else if($data->transaction->bank=="fast")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);
            
            $data_json->payment_type = 'bank_transfer';
            $data_json->va_number = $data1['data']['textual_codes']['DBS Account No'];
            $data_json->redirect = $data->transaction->finish_url;
        }
        else if($data->transaction->bank=="alfamart")
        {

            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'customer' => [
                    'name' => $data->contact->name,
                    'phone_number' => $data->contact->phone,
                    'email' => $data->contact->email
                ],
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];


            
            $data1 = self::make_request('post','/v1/payments',$body);
            
           
            $data_json->payment_type = 'cash';
            $data_json->va_number = $data1['data']['textual_codes']['pay_code'];
            $data_json->redirect = $data->transaction->finish_url;
        }
        else if($data->transaction->bank=="promptpay")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];


            $data1 = self::make_request('post','/v1/payments',$body);
            $qrcode = $data1['data']['visual_codes']['qrcode_image_base64'];
            $qrcode = str_ireplace('data:image/png;base64,','',$qrcode);
            
            $data_json->payment_type = 'qrcode';
            $data_json->qrcode = $qrcode;
            $data_json->redirect = $data->transaction->finish_url;
        }
        else if($data->transaction->bank=="qris")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];


            $data1 = self::make_request('post','/v1/payments',$body);
            
            $qrcode = $data1['data']['visual_codes']['qrcode_image_base64'];
            $qrcode = str_ireplace('data:image/png;base64,','',$qrcode);
            
            $data_json->payment_type = 'qrcode';
            $data_json->qrcode = $qrcode;
            $data_json->redirect = $data->transaction->finish_url;
        }
        else
        {

            $body = [
                'amount' => $data->transaction->amount,
                'currency' => $data->transaction->currency,
                'customer' => [
                    'name' => $data->contact->name,
                    'phone_number' => $data->contact->phone,
                    'email' => $data->contact->email
                ],
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);
            $paycode = '';
            if(isset($data1['data']['textual_codes']['pay_code'])) $paycode = $data1['data']['textual_codes']['pay_code'];

            $data_json->payment_type = 'bank_transfer';
            $data_json->va_number = $paycode;
            $data_json->redirect = $data->transaction->finish_url;
        }

        
        $data_json->bank_name = $payment->bank_name;
        $data_json->bank_code = $payment->bank_code;
        $data_json->expiration_date = $data->transaction->date_expired;
        $data_json->order_id = $data1['data']['id'];
        
        $status_json->id = '1';
        $status_json->message = 'success';
        
        $response_json->status = $status_json;
        $response_json->data = $data_json;

        return $response_json;
    }

    public static function make_request($method, $path, $body = null) {
        
        $base_url = self::rapydApiEndpoint();
        $access_key = self::env_rapydAccessKey();     // The access key received from Rapyd.
        $secret_key = self::env_rapydSecretKey();     // Never transmit the secret key by itself.

        $idempotency = self::generate_string();       // Unique for each request.
        $http_method = $method;                       // Lower case.
        $salt = self::generate_string();              // Randomly generated for each request.
        $date = new \DateTime();
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