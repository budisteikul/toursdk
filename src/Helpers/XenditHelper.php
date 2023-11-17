<?php
namespace budisteikul\toursdk\Helpers;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use budisteikul\toursdk\Helpers\GeneralHelper;
use Carbon\Carbon;

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
            $data->transaction->mins_expired = 30;
            $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

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
                $data_json->order_id = $data1->id;
                $data_json->redirect = $data1->actions->mobile_web_checkout_url;
                $data_json->authorization_id = $data1->reference_id;

                $status_json->id = '1';
                $status_json->message = 'success';
            }
            
        }

        if($data->transaction->bank=="qris")
        {
            $data->transaction->mins_expired = 30;
            $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

            $amount = round($data->transaction->amount);
            $expired_at = GeneralHelper::dateFormat($data->transaction->date_expired,12);
            $data1 = (new self)->createQrcode($amount,$expired_at);

            if(isset($data1->error_code))
            {
                $status_json->id = '0';
                $status_json->message = 'error';
            }
            else
            {
                $data_json->order_id = $data1->id;
                $data_json->authorization_id = $data1->reference_id;
                $data_json->qrcode = $data1->qr_string;

                $status_json->id = '1';
                $status_json->message = 'success';
            }
        }

        if($data->transaction->bank=="bss")
        {
            $data->transaction->mins_expired = 30;
            $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

            $amount = round($data->transaction->amount);
            $expired_at = GeneralHelper::dateFormat($data->transaction->date_expired,12);
            $name = $data->contact->name;
            $bank_code = 'SAHABAT_SAMPOERNA';

            $data1 = (new self)->createVirtualAccount($bank_code,$amount,$name,$expired_at);

            if(isset($data1->error_code))
            {
                $status_json->id = '0';
                $status_json->message = 'error';
            }
            else
            {
                $data_json->order_id = $data1->id;
                $data_json->va_number = $data1->account_number;
                $data_json->authorization_id = $data1->external_id;

                $status_json->id = '1';
                $status_json->message = 'success';
            }
        }

        if($data->transaction->bank=="invoice")
        {
            
            $amount = round($data->transaction->amount);
            $confirmation_code = $data->transaction->confirmation_code;

            $data1 = (new self)->createInvoice($confirmation_code,$amount);

            if(isset($data1->error_code))
            {
                $status_json->id = '0';
                $status_json->message = 'error';
            }
            else
            {
                $data_json->payment_type = 'bank_redirect';
                $data_json->redirect = $data1->invoice_url;
                $data_json->order_id = $data1->id;
                $data_json->authorization_id = $data1->external_id;
                $data_json->success_redirect_url = self::env_appUrl() . $data->transaction->finish_url;
                $data_json->failure_redirect_url = self::env_appUrl() . $data->transaction->finish_url;

                $status_json->id = '1';
                $status_json->message = 'success';
            }
        }

        if($data->transaction->bank=="card")
        {
            $amount = $data->transaction->amount;
            //$amount = 10059;
            $token_id = $data->transaction->order_id;
            $external_id = $data->transaction->confirmation_code;

            $data_json = new \stdClass();
            $status_json = new \stdClass();
            $response_json = new \stdClass();
      
            $data1 = (new self)->createChargeCard($token_id,$external_id,$amount);

            if($data1->status=="CAPTURED")
            {
                 $status_json->id = '1';
                 $status_json->message = $data1;
                 $data_json->order_id = $data->transaction->order_id;
                 $data_json->authorization_id = $external_id;
                 $data_json->payment_status = 2;
            }
            else
            {
                 $status_json->id = '0';
                 $message = '';
                 if($data1->failure_reason=="EXPIRED_CARD") $message = 'The card has expired.';
                 if($data1->failure_reason=="ISSUER_SUSPECT_FRAUD") $message = 'The card has been declined by the issuing bank due to potential fraud suspicion.';
                 if($data1->failure_reason=="DECLINED_BY_PROCESSOR") $message = 'The card has been declined by the processor.';
                 if($data1->failure_reason=="INSUFFICIENT_BALANCE") $message = 'The card does not have enough balance.';
                 if($data1->failure_reason=="STOLEN_CARD") $message = 'The card has been marked as stolen.';
                 if($data1->failure_reason=="INACTIVE_OR_UNAUTHORIZED_CARD") $message = 'The card is inactive or unauthorized to perform the transaction.';
                 if($data1->failure_reason=="PROCESSOR_ERROR") $message = 'The charge failed because there\'s an integration issue between the card processor and the bank.';
                 if($data1->failure_reason=="INVALID_CVV") $message = 'The card is declined due to unmatched CVV / CVC';
                 if($data1->failure_reason=="DECLINED_BY_ISSUER") $message = 'The card is declined by the issuing bank';
                 $status_json->message = $message;
            }

        }

        $data_json->expiration_date = $data->transaction->date_expired;
        
        $response_json->status = $status_json;
        $response_json->data = $data_json;

        return $response_json;
    }

    public function createChargeCard($token_id,$external_id,$amount)
    {
        $data = new \stdClass();
        $data->external_id = $external_id;
        $data->amount = $amount;
        $data->token_id = $token_id;
        return json_decode($this->POST('/credit_card_charges',$data));
    }

    public function createInvoice($confirmation_code,$amount)
    {
        $data = new \stdClass();
        $data->external_id = $confirmation_code;
        $data->amount = $amount;
        $data->payment_methods = ['CREDIT_CARD'];
        return json_decode($this->POST('/v2/invoices',$data));
    }

    public function createQrcode($amount,$expired_at)
    {
        $data = new \stdClass();
        $data->reference_id = Uuid::uuid4()->toString();
        $data->type = 'DYNAMIC';
        $data->amount = $amount;
        $data->currency = 'IDR';
        $data->expired_at = $expired_at;

        return json_decode($this->POST('/qr_codes',$data,['api-version: 2022-07-31']));
    }

    public function createVirtualAccount($bank_code,$amount,$name,$expired_at)
    {
        $data = new \stdClass();
        $data->external_id = Uuid::uuid4()->toString();
        $data->bank_code = $bank_code;
        $data->name = $name;
        $data->is_closed = true;
        $data->expected_amount = $amount;
        $data->expiration_date = $expired_at;

        return json_decode($this->POST('/callback_virtual_accounts',$data));
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
