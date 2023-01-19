<?php
namespace budisteikul\toursdk\Helpers;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class XenditHelper {

    private $xendit;

    public function __construct() {

    	$this->xendit = new \stdClass();
    	$this->xendit->secret_key = env("XENDIT_SECRET_KEY");
        $this->xendit->endpoint = 'https://api.xendit.co';
    }

    public static function env_appUrl()
    {
        return env("APP_URL");
    }

    public static function createPayment($data)
    {
        $data_json = new \stdClass();
        $status_json = new \stdClass();
        $response_json = new \stdClass();

        if($data->transaction->bank=="dana")
        {
            $amount = round($data->transaction->amount);
            $success_redirect_url = self::env_appUrl().$data->transaction->finish_url;
            $data1 = (new self)->createEWalletDanaCharge($amount,$success_redirect_url);
            
            if(isset($data1->error_code))
            {
                $status_json->id = '0';
                $status_json->message = 'error';
            }
            else
            {
                $data_json->redirect = $data1->actions->mobile_web_checkout_url;
                $data_json->authorization_id = $data1->reference_id;

                $status_json->id = '1';
                $status_json->message = 'success';
            }
            
        }
        
        $response_json->status = $status_json;
        $response_json->data = $data_json;

        return $response_json;
    }

    public function createQrcode($amount)
    {
        $data = new \stdClass();
        $data->reference_id = Uuid::uuid4()->toString();
        $data->type = 'DYNAMIC';
        $data->amount = $amount;
        $data->currency = 'IDR';

        return json_decode($this->POST('/qr_codes',$data));
    }

    public function createEWalletOvoCharge($amount,$mobile_number)
    {
        $data = new \stdClass();
        $data->reference_id = Uuid::uuid4()->toString();
        $data->currency = 'IDR';
        $data->amount = $amount;
        $data->checkout_method = 'ONE_TIME_PAYMENT';
        $data->channel_code = 'ID_OVO';
        $data->channel_properties = new \stdClass();
        $data->channel_properties->mobile_number = $mobile_number;
        
        return json_decode($this->POST('/ewallets/charges',$data));
    }

    public function createEWalletDanaCharge($amount,$success_redirect_url)
    {
        $data = new \stdClass();
        $data->reference_id = Uuid::uuid4()->toString();
        $data->currency = 'IDR';
        $data->amount = $amount;
        $data->checkout_method = 'ONE_TIME_PAYMENT';
        $data->channel_code = 'ID_DANA';
        $data->channel_properties = new \stdClass();
        $data->channel_properties->success_redirect_url = $success_redirect_url;
        return json_decode($this->POST('/ewallets/charges',$data));
    }

    private function POST($url,$data){
        return $this->curl('POST',$url,$data);
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
        curl_setopt($ch, CURLOPT_URL, $this->xendit->endpoint."$curl_url");

        $headerArray[] = "Authorization: Basic ". base64_encode($this->xendit->secret_key.':');
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
