<?php
namespace budisteikul\toursdk\Helpers;
use Zxing\QrReader;
use Storage;

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
                $data->bank_name = "dbs";
                $data->bank_code = "7171";
                $data->bank_payment_type = "sg_paynow_bank";
            break;
            case "cimb":
                $data->bank_name = "cimb niaga";
                $data->bank_code = "022";
                $data->bank_payment_type = "id_cimb_bank";
            break;
            case "permata":
                $data->bank_name = "permata";
                $data->bank_code = "013";
                $data->bank_payment_type = "id_permata_bank";
            break;
            case "mandiri":
                $data->bank_name = "mandiri";
                $data->bank_code = "008";
                $data->bank_payment_type = "id_mandiri_bank";
            break;
            case "bri":
                $data->bank_name = "bri";
                $data->bank_code = "002";
                $data->bank_payment_type = "id_bri_bank";
            break;
            case "bni":
                $data->bank_name = "bni";
                $data->bank_code = "009";
                $data->bank_payment_type = "id_bni_bank";
            break;
            case "danamon":
                $data->bank_name = "danamon";
                $data->bank_code = "011";
                $data->bank_payment_type = "id_danamon_bank";
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

        if($payment->bank_payment_type=="sg_paynow_bank")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => 'SGD',
                'payment_method' => [
                    'type' => 'sg_paynow_bank',
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);

            $qrcode = $data1['data']['visual_codes']['PayNow QR'];
            list($type, $qrcode) = explode(';', $qrcode);
            list(, $qrcode)      = explode(',', $qrcode);
            $contents = base64_decode($qrcode);

            $path = date('YmdHis');
            $disk = Storage::disk('gcs');
            $disk->put('qrcode/'. $path .'/'.$data1['data']['id'].'.png', $contents);
            $url = $disk->url('qrcode/'. $path .'/'.$data1['data']['id'].'.png');
            $qrcode = new QrReader($url);

            $response->payment_type = 'qris';
            $response->qrcode = $qrcode->text();
        }
        else if($payment->bank_payment_type=="sg_fast_bank")
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => 'SGD',
                'payment_method' => [
                    'type' => 'sg_fast_bank',
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);
            $response->payment_type = 'bank_transfer';
            $response->va_number = $data1['data']['textual_codes']['DBS Account No'];
        }
        else
        {
            $body = [
                'amount' => $data->transaction->amount,
                'currency' => 'IDR',
                'payment_method' => [
                    'type' => $payment->bank_payment_type,
                    'fields' => []
                ]
            ];

            $data1 = self::make_request('post','/v1/payments',$body);

            $response->payment_type = 'bank_transfer';
            $response->va_number = $data1['data']['textual_codes']['pairing_code'];
        }

        //$response->authorization_id = $data1['data']['id'];
        $response->bank_name = $payment->bank_name;
        $response->bank_code = $payment->bank_code;
        $response->redirect = $data->transaction->finish_url;
        $response->expiration_date = $data->transaction->date_expired;
        $response->order_id = $data1['data']['id'];
        
        return $response;
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