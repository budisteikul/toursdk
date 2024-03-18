<?php
namespace budisteikul\toursdk\Helpers;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use budisteikul\toursdk\Helpers\GeneralHelper;
use Carbon\Carbon;
use budisteikul\toursdk\Helpers\LogHelper;
use budisteikul\toursdk\Helpers\FirebaseHelper;

class XenditHelper {

    private $xendit;

    public function __construct() {

    	$this->xendit = new \stdClass();
    	$this->xendit->secret_key = env("XENDIT_SECRET_KEY");
        $this->xendit->endpoint = 'https://api.xendit.co';
    }

    public static function createPayment($data)
    {
        $data_json = new \stdClass();
        $status_json = new \stdClass();
        $response_json = new \stdClass();

        if($data->transaction->bank=="qris")
        {
            $data->transaction->mins_expired = 30;
            $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

            $expired_at = GeneralHelper::dateFormat($data->transaction->date_expired,12);
            $data1 = (new self)->createQrcode($data->transaction->amount,$expired_at);

            if(isset($data1->error_code))
            {
                $status_json->id = '0';
                $status_json->message = 'error';
            }
            else
            {
                $data_json->authorization_id = $data1->id;
                $data_json->order_id = $data1->reference_id;
                $data_json->qrcode = $data1->qr_string;

                $status_json->id = '1';
                $status_json->message = 'success';
            }
        }

        if($data->transaction->bank=="bss")
        {
            $data->transaction->mins_expired = 30;
            $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);

            $expired_at = GeneralHelper::dateFormat($data->transaction->date_expired,12);
            $name = $data->contact->name;
            $bank_code = 'SAHABAT_SAMPOERNA';

            $data1 = (new self)->createVirtualAccount($bank_code,$data->transaction->amount,$name,$expired_at);

            if(isset($data1->error_code))
            {
                $status_json->id = '0';
                $status_json->message = 'error';
            }
            else
            {
                $data_json->authorization_id = $data1->id;
                $data_json->va_number = $data1->account_number;
                $data_json->order_id = $data1->external_id;

                $status_json->id = '1';
                $status_json->message = 'success';
            }
        }

        if($data->transaction->bank=="invoice")
        {
            
            $data1 = (new self)->createInvoice($data->transaction->amount,$data->transaction->param1,$data->transaction->second_expired);

            if(isset($data1->error_code))
            {
                $status_json->id = '0';
                $status_json->message = 'error';
            }
            else
            {
                $data_json->redirect = $data1->invoice_url;
                $data_json->authorization_id = $data1->id;
                $data_json->order_id = $data1->external_id;
                $data_json->success_redirect_url = $data->transaction->finish_url_full;
                $data_json->failure_redirect_url = $data->transaction->finish_url_full;

                $status_json->id = '1';
                $status_json->message = 'success';
            }
        }

        if($data->transaction->bank=="card")
        {
            $data_json = new \stdClass();
            $status_json = new \stdClass();
            $response_json = new \stdClass();
            
            

            $data1 = (new self)->createChargeCard($data->transaction->param1,$data->transaction->amount);
            LogHelper::log($data1,'xdt-charge');

            if($data1->status=="CAPTURED")
            {
                 $status_json->id = '1';
                 $status_json->message = $data1;
                 $data_json->authorization_id = $data1->id;
                 $data_json->order_id = $data1->external_id;
                 $data_json->payment_status = 2;
            }
            else
            {
                 $status_json->id = '0';
                 $message = 'Fail to charge card.  Please change charge payment method into USD and try again';
                 if($data1->failure_reason=="EXPIRED_CARD") $message = 'The card has expired.';
                 if($data1->failure_reason=="INSUFFICIENT_BALANCE") $message = 'The card does not have enough balance.';
                 if($data1->failure_reason=="INVALID_CVV") $message = 'The card is declined due to unmatched CVV / CVC.';
                 $status_json->message = $message;
            }

        }

        $data_json->expiration_date = $data->transaction->date_expired;
        
        $response_json->status = $status_json;
        $response_json->data = $data_json;

        return $response_json;
    }

    public function getTokenCard($token_id)
    {
        return json_decode($this->GET('/credit_card_tokens/'.$token_id));
    }

    public function createChargeCard($token_id,$amount)
    {
        $data = new \stdClass();
        $data->external_id = Uuid::uuid4()->toString();
        $data->amount = $amount;
        $data->token_id = $token_id;

        
        $billing = FirebaseHelper::read('billing/'.$token_id);
        $country = "";
        if(isset($billing->country)) $country = $billing->country;
        if($country=="US" || $country=="CA" || $country=="GB")
        {
            $given_name = "";
            $surname = "";
            $street_line1 = "";
            $postal_code = "";

            if(isset($billing->given_name)) $given_name = $billing->given_name;
            if(isset($billing->surname)) $surname = $billing->surname;
            if(isset($billing->street_line1)) $street_line1 = $billing->street_line1;
            if(isset($billing->postal_code)) $postal_code = $billing->postal_code;


            $data->billing_details = new \stdClass();
            $data->billing_details->given_names = $given_name;
            $data->billing_details->surname = $surname;
            $data->billing_details->address = new \stdClass();
            $data->billing_details->address->country = $country;
            $data->billing_details->address->street_line1 = $street_line1;
            $data->billing_details->address->postal_code = $postal_code;

        }
        //FirebaseHelper::delete('billing/'.$token_id);
        

        return json_decode($this->POST('/credit_card_charges',$data));
    }
    
    public function createRefund($shoppingcart)
    {
        $payment_type = $shoppingcart->shoppingcart_payment->payment_type;
        $amount = $shoppingcart->shoppingcart_payment->amount;
        $external_id = $shoppingcart->shoppingcart_payment->order_id;
        $token_id = $shoppingcart->shoppingcart_payment->authorization_id;

        if($payment_type=="card")
        {
            $data = new \stdClass();
            $data->external_id = $external_id;
            $data->amount = $amount;
            return json_decode($this->POST('/credit_card_charges/'.$token_id.'/refunds',$data,['api-version: 2019-05-01','X-IDEMPOTENCY-KEY: '.$external_id]));
        }

        if($payment_type=="qrcode")
        {
            $data = new \stdClass();
            $data->amount = $amount;
            return json_decode($this->POST('/qr_codes/payments/'.$token_id.'/refunds',$data,['api-version: 2019-05-01','X-IDEMPOTENCY-KEY: '.$external_id]));
        }
    }

    public function createInvoice($amount, $payment_method=['CREDIT_CARD'], $invoice_duration)
    {
        $data = new \stdClass();
        $data->external_id = Uuid::uuid4()->toString();
        $data->amount = $amount;
        $data->payment_methods = $payment_method;
        $data->invoice_duration = $invoice_duration;
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
