<?php
namespace budisteikul\toursdk\Helpers;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use budisteikul\toursdk\Helpers\GeneralHelper;

class FinmoHelper {

    private $finmo;

    public function __construct() {

    	$this->finmo = new \stdClass();
    	$this->finmo->access_key = env("FINMO_ACCESS_KEY");
        $this->finmo->secret_key = env("FINMO_SECRET_KEY");
        $this->finmo->endpoint = 'https://api.finmo.net';
    }

    public static function env_appUrl()
    {
        return env("APP_URL");
    }

    public static function payment($bank)
    {
        $data = new \stdClass();
        switch($bank)
        {
            case "promptpay":
                $data->bank_name = "promptpay";
                $data->bank_code = "";
                $data->bank_country = "TH";
                $data->bank_payment_type = "qrcode";
                $data->bank_provider = "finmo";
                $data->bank_payment_method = "th_bank_promptpaycash_thb";
            break;
            case "paynow":
                $data->bank_name = "paynow";
                $data->bank_code = "";
                $data->bank_country = "SG";
                $data->bank_payment_type = "qrcode";
                $data->bank_provider = "finmo";
                $data->bank_payment_method = "sg_bank_paynow_sqd";
            break;
            case "npp":
                $data->bank_name = "npp";
                $data->bank_code = "";
                $data->bank_country = "AU";
                $data->bank_payment_type = "other";
                $data->bank_provider = "finmo";
                $data->bank_payment_method = "au_bank_npp";
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
        $payment = self::payment($data->transaction->bank);

        $data_json = new \stdClass();
        $status_json = new \stdClass();
        $response_json = new \stdClass();
        
        $data->transaction->mins_expired = 60;
        $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

        $payin = (new self)->createPayin($data,$payment);
        //print_r($payin);
        //exit();

        if($payment->bank_payment_type=="qrcode")
        {
            $data_json->payment_type = 'qrcode';
            $data_json->qrcode = $qrcode;
            $data_json->redirect = $data->transaction->finish_url;
        }

        if($payment->bank_payment_type=="other")
        {
            $data_json->payment_type = 'other';
            $data_json->payment_description = '
            <input type="hidden" id="payId" value="'. $payin->data->pay_code->text .'">
            <div>Your PayID for this transaction :</div>
            <div class="mb-2"><b>'. $payin->data->pay_code->text .'</b> <button onclick="copyToClipboard(\'#payId\')" id="payId_button" data-toggle="tooltip" data-placement="right" title="Copied" data-trigger="click" class="btn btn-light btn-sm invoice-hilang"><i class="far fa-copy"></i></button>
            </div>
            ';
            $data_json->redirect = $data->transaction->finish_url;
        }

        $data_json->authorization_id = $payin->data->payin_id;
        $data_json->order_id = $data->transaction->id;
        $data_json->bank_name = $payment->bank_name;
        $data_json->bank_code = $payment->bank_code;
            
        $data_json->expiration_date = $data->transaction->date_expired;

        $status_json->id = '1';
        $status_json->message = 'success';
        
        $response_json->status = $status_json;
        $response_json->data = $data_json;
        
        return $response_json;
    }

    public function createPayin($data,$payment)
    {
        $body = new \stdClass();
        $body->amount = (float)$data->transaction->amount;
        $body->currency = $data->transaction->currency;
        $body->payin_method_name = $payment->bank_payment_method;
        $body->webhook_url = env('APP_API_URL') .'/payment/finmo/confirm';

        return json_decode($this->POST('/v1/payin',$body));
    }

    private function POST($url,$data,$headers=NULL){
        return $this->curl('POST',$url,$data,$headers);
    }

    private function GET($url){
        return $this->curl('GET',$url);
    }
    
    private function DELETE($url){
        return $this->curl('DELETE',$url);
    }
    
    private function PUT($url){
        return $this->curl('PUT',$url);
    }

    private function curl($mode, $curl_url,$data=NULL,$headers=NULL)
    {
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_URL, $this->finmo->endpoint."$curl_url");

        $headerArray[] = "Authorization: Basic ". base64_encode($this->finmo->access_key.':'.$this->finmo->secret_key);

        if($mode=='POST'){

            $payload = json_encode($data);

            $headerArray[] = "Content-Type: application/json";
            $headerArray[] = 'Content-Length: ' . strlen($payload);
            
            if($headers){
                foreach($headers as $header){
                    $headerArray[] = $header;
                }
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mode); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        
        $response = curl_exec($ch);
        
        if($response === false){
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close ($ch);
        
        return  $response;
    }

    
}
?>
