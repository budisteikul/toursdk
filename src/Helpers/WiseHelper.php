<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Http\Request;

class WiseHelper {

    private $tw;
    private $OTT;

    public function __construct() {
    	$this->tw = new \stdClass();
    	$this->tw->profileId = env("WISE_ID");
    	$this->tw->api_key = env("WISE_TOKEN");
    	$this->tw->priv_pem = env("WISE_PRIVATE_KEY");
    	if(env("WISE_ENV")=="production")
        {
            $this->tw->url = "https://api.transferwise.com";
        }
        else
        {
            $this->tw->url = "https://api.sandbox.transferwise.tech";
        }
    }

    public function getRecipientAccounts(
            $profileId=null,        //[optional] Personal or business profile id  
            $currency=null          //[optional] a 3-char currency code. e.g. 'EUR'.
            ){
        $profileId && $data[] = array('profileId' => $this->tw->profileId);
        $currency  && $data[] = array('currency' => 'IDR');
        $data && ($args='?'.http_build_query($data));
        return json_decode($this->GET('/v1/accounts'.$args));
    }

    //https://api.sandbox.transferwise.tech/v3/profiles/{profileId}/quotes
    public function postCreateQuote($sourceAmount){
        $data = new \stdClass();
        $data->profileId		= $this->tw->profileId;
        $data->sourceCurrency	= 'USD';
        $data->targetCurrency	= 'IDR';
        $data->sourceAmount		= $sourceAmount;
        $data->payOut			= 'BALANCE';
        return json_decode($this->POST('/v3/profiles/'.$data->profileId.'/quotes',$data));
    }

    public function postCreateQuoteV1(
            $type,               	//'BALANCE_PAYOUT' for payments or 'BALANCE_CONVERSION' for conversion between balances
            $sourceCurrency,        //a 3-char currency code. e.g. 'EUR'.
            $targetCurrency,        //a 3-char currency code. e.g. 'EUR'.
            $sourceAmount=null,     //Amount in source currency. If specified, $targetAmount must be null.
            $targetAmount=null      //Amount in target currency. If specified, $sourceAmount must be null. 
            ){
        $data = new \stdClass();
        $data->profile           = $this->tw->profileId;
        $data->target            = $targetCurrency;
        $data->source            = $sourceCurrency;
        $data->rateType          = 'FIXED';
        if($targetAmount) $data->targetAmount = $targetAmount;
        else              $data->sourceAmount = $sourceAmount;
        $data->type              = $type;
       
        return json_decode($this->POST('/v1/quotes',$data));
    }

    public function postCreateTransfer($quoteId){
        $data = new \stdClass();
        $data->targetAccount	= env("WISE_BANK_ID");
        $data->quoteUuid	    = $quoteId;
        $data->customerTransactionId    = $this->createUUID();

        $data->details = new \stdClass();
        //$data->details->reference       = $reference;
        $data->details->transferPurpose = 'verification.transfers.purpose.other';
        $data->details->sourceOfFunds = 'verification.source.of.funds.other';
        return json_decode($this->POST('/v1/transfers',$data));
    }

    public function postCreateTransferV1(
            //$targetAccount,         //recipient account id 
            $quoteId,               //quote id
            $reference,             //Recipient will see this reference text in their bank statement
            $transferPurpose =null, //[Conditional] see: https://api-docs.transferwise.com/#transfers-requirements
            $sourceOfFunds =null    //[Conditional]see: https://api-docs.transferwise.com/#transfers-requirements
            ){
        $data = new \stdClass();
        $data->targetAccount            = env("WISE_BANK_ID");
        $data->quote                    = $quoteId;
        $data->customerTransactionId    = $this->createUUID();
        $data->details =  new \stdClass();
        $data->details->reference       = $reference;
        $transferPurpose && $data->details->transferPurpose = 'verification.transfers.purpose.other';
        $sourceOfFunds && $data->details->sourceOfFunds = 'verification.source.of.funds.other';
        return json_decode($this->POST('/v1/transfers',$data));
    }

    
    
    public function postFundTransfer(
            $transferId             //transferID from postCreateTransfer()
            ){
        $data = new \stdClass();
        $data->type     = 'BALANCE';
        
        return json_decode($this->POST("/v3/profiles/".$this->tw->profileId."/transfers/$transferId/payments",$data));
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
        
        //See if need to resend because of SCA
        if(!empty($this->OTT)){
            //We have received a One Time Token
            $SCA=json_decode($response);
            if($SCA->status==403 && !empty($SCA->path)){
                if(version_compare(PHP_VERSION, '5.4.8') >= 0){
                  
                  $pkeyid = openssl_pkey_get_private('file://'.$this->tw->priv_pem);
                  openssl_sign($this->OTT, $Xsignature, $pkeyid,OPENSSL_ALGO_SHA256);
                  openssl_free_key($pkeyid);
                  $Xsignature= base64_encode( $Xsignature);
                } else {
                  //Requires access to shell commands
                  $Xsignature= shell_exec("printf '$this->OTT' | openssl sha256 -sign ".$this->tw->priv_pem." | base64 -w 0") ;
                }
                $headers[] = "x-2fa-approval: $this->OTT";
                $headers[] = "X-Signature: $Xsignature";
                $response = $this->curl($mode, $SCA->path,$data,$headers);
            }
        }
        
        return  $response;
    }

    private function createUUID() {
      return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
      );
    }
    
}
?>