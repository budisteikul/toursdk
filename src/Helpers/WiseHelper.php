<?php
namespace budisteikul\toursdk\Helpers;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Illuminate\Support\Facades\Storage;

class WiseHelper {

    private $tw;
    private $OTT;

    public function __construct() {
    	$this->tw = new \stdClass();
    	$this->tw->profileId = env("WISE_ID");
    	$this->tw->api_key = env("WISE_TOKEN");
    	$this->tw->bank_id = env("WISE_BANK_ID");
        
    	if(env("WISE_ENV")=="production")
        {
            $this->tw->url = "https://api.transferwise.com";
            $this->tw->priv_pem = Storage::disk('gcs')->get('credentials/wise/private.pem');
            $this->tw->webhook_pem = Storage::disk('gcs')->get('credentials/wise/webhook.pem');
        }
        else
        {
            $this->tw->url = "https://api.sandbox.transferwise.tech";
            $this->tw->priv_pem = Storage::disk('gcs')->get('credentials/wise/sandbox_private.pem');
            $this->tw->webhook_pem = Storage::disk('gcs')->get('credentials/wise/sandbox_webhook.pem');
        }
    }

    public function getRecipientAccounts(){
        return json_decode($this->GET('/v1/accounts?profile='. $this->tw->profileId .'&currency=IDR'));
    }

    public function getBalanceAccounts(){
        return json_decode($this->GET('/v4/profiles/'. $this->tw->profileId .'/balances?types=STANDARD'));
    }

    
    public function postCreateQuote($sourceAmount,$sourceCurrency){
        $data = new \stdClass();
        $data->profileId		= $this->tw->profileId;
        $data->sourceCurrency	= $sourceCurrency;
        $data->targetCurrency	= 'IDR';
        $data->sourceAmount		= $sourceAmount;
        $data->payOut			= 'BALANCE';
        return json_decode($this->POST('/v3/profiles/'.$data->profileId.'/quotes',$data));
    }

    public function postCreateTransfer($quoteId,$targetAccount=null,$reference=null){
        $data = new \stdClass();
        if($targetAccount==null) $targetAccount = $this->tw->bank_id;
        $data->targetAccount = $targetAccount;
        $data->quoteUuid	    = $quoteId;
        $data->customerTransactionId    = Uuid::uuid4()->toString();

        $data->details = new \stdClass();
        if($reference!=null) $data->details->reference = $reference;
        
        $data->details->transferPurpose = 'verification.transfers.purpose.other';
        $data->details->sourceOfFunds = 'verification.source.of.funds.other';
        return json_decode($this->POST('/v1/transfers',$data));
    }

    public function postFundTransfer($transferId,$type=null)
    {
        $data = new \stdClass();
        if($type==null) $type = "BALANCE";
        $data->type     = $type;
        return json_decode($this->POST("/v3/profiles/".$this->tw->profileId."/transfers/$transferId/payments",$data));
    }

    public function checkSignature($json,$signature)
    {
        $status = false;
        $pub_key = $this->tw->webhook_pem;
        $verify = openssl_verify ($json , base64_decode($signature) ,$pub_key, OPENSSL_ALGO_SHA256);
        if($verify) $status = true;
        return $status;
    }

    public function simulateAddFund($amount,$currency)
    {
        $data = new \stdClass();
        $data->profileId = $this->tw->profileId;
        $data->balanceId = '126108';
        $data->currency = $currency;
        $data->amount = $amount;
        return json_decode($this->POST("/v1/simulation/balance/topup",$data));
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

    private function headerLineCallback($curl, $headerLine){
    	$len = strlen($headerLine);
        $header = explode(':', $headerLine, 2);
        if (count($header) < 2) // ignore invalid headers
           return $len;
           
        if(strtolower(trim($header[0])) == 'x-2fa-approval')
            $this->OTT = trim($header[1]);

        return $len;
    }

    private function curl($mode, $curl_url,$data=NULL,$headers=NULL)
    {
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this,'headerLineCallback'));
        curl_setopt($ch, CURLOPT_URL, $this->tw->url."$curl_url");
        $headerArray[] = "Authorization: Bearer ".$this->tw->api_key;
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
        
        //Reset One Time Token
        $this->OTT = ''; 
        
        $response = curl_exec($ch);
        
        if($response === false){
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close ($ch);
        
        
        if(!empty($this->OTT)){
            
            $SCA=json_decode($response);
            if($SCA->status==403 && !empty($SCA->path)){
                
                openssl_sign($this->OTT, $Xsignature, $this->tw->priv_pem, OPENSSL_ALGO_SHA256);
                openssl_free_key($this->tw->priv_pem);
                $Xsignature= base64_encode( $Xsignature);
                
                $headers[] = "x-2fa-approval: $this->OTT";
                $headers[] = "X-Signature: $Xsignature";
                $response = $this->curl($mode, $SCA->path,$data,$headers);
            }
        }
        
        return  $response;
    }

    
}
?>
