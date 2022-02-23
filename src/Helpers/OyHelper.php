<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Models\Disbursement;
use budisteikul\toursdk\Helpers\ImageHelper;
use Carbon\Carbon;

class OyHelper {
	
  public static function env_oyApiKey()
  {
        return env("OY_API_KEY");
  }

  public static function env_oyUsername()
  {
        return env("OY_USERNAME");
  }

  public static function env_oyEnv()
  {
        return env("OY_ENV");
  }

  public static function oyApiEndpoint()
  {
        if(self::env_oyEnv()=="production")
        {
            $endpoint = "https://partner.oyindonesia.com";
        }
        else
        {
            $endpoint = "https://api-stg.oyindonesia.com";
        }
        return $endpoint;
  }

  //https://pay-stg.oyindonesia.com/909feb39-7d71-4a84-af7b-3f30da24b93c
  public static function oyLink($token)
  {
        if(self::env_oyEnv()=="production")
        {
            $endpoint = "https://pay.oyindonesia.com/". $token;
        }
        else
        {
            $endpoint = "https://pay-stg.oyindonesia.com/". $token;
        }
        return $endpoint;
  }

  public static function oyCheckoutEndpoint()
  {
        if(self::env_oyEnv()=="production")
        {
            $endpoint = "https://checkout.oyindonesia.com";
        }
        else
        {
            $endpoint = "https://checkout-stg.oyindonesia.com";
        }
        return $endpoint;
  }


  public static function get_transaction_id(){
      $uuid = "DISB-". date('Ymd') . rand(100,999);
      while( Disbursement::where('transaction_id','=',$uuid)->first() ){
          $uuid = "DISB-". date('Ymd') . rand(100,999);
      }
      return $uuid;
  }

  public static function bankCode($bank_name)
  {
    switch($bank_name)
    {
      case "btpn":
        $bank_code = "213";
      break;
      case "bri":
        $bank_code = "002";
      break;
      case "cimb":
        $bank_code = "022";
      break;
      case "mandiri":
        $bank_code = "008";
      break;
      case "permata":
        $bank_code = "013";
      break;
      case "bni":
        $bank_code = "009";
      break;
    }
    return $bank_code;
  }

  public static function createDisbursement($disbursement)
  {
        $endpoint = self::oyApiEndpoint() ."/api/remit";
        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'x-oy-username' => self::env_oyUsername(),
              'x-api-key' => self::env_oyApiKey(),
          ];

        $data = [
          'recipient_bank' => $disbursement->bank_code,
          'recipient_account' => $disbursement->account_number,
          'amount' => $disbursement->amount,
          'note' => $disbursement->reference,
          'partner_trx_id' => $disbursement->transaction_id,
        ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        
        return $data;
       
  }

  public static function createPayment($shoppingcart,$bank)
  {
        $response = new \stdClass();

        if($bank=="qris")
        {
          $data1 = self::createSnap($shoppingcart);
          $data2 = self::createCharge($shoppingcart,$data1->snaptoken,$bank);
          $qrcode = ImageHelper::uploadQrcodeCloudinary($data2->data->qris_url);
          $response->payment_type = 'ewallet';
          $response->bank_name = 'shopeepay';
          $response->qrcode = $qrcode['secure_url'];
          $response->link = self::oyLink($data1->snaptoken);
        }
        else
        {
          $data1 = self::createSnap($shoppingcart);
          $data2 = self::createCharge($shoppingcart,$data1->snaptoken,$bank);
          $data3 = self::status($data1->snaptoken);
          $response->payment_type = 'bank_transfer';
          $response->bank_name = $bank;
          $response->bank_code = $data3->data->sender_bank;
          $response->va_number = $data3->data->va_number;
          $response->link = self::oyLink($data1->snaptoken);
        }
        
        
        return $response;
  }

  public static function status($token)
  {
        $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/status/enc/'. $token;

        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json'
          ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('GET',$endpoint);

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        
        return $data;
  }

  public static function createCharge($shoppingcart,$token,$bank)
  {
        $first_name = BookingHelper::get_answer($shoppingcart,'firstName');
        $last_name = BookingHelper::get_answer($shoppingcart,'lastName');
        $email = BookingHelper::get_answer($shoppingcart,'email');
        $phone = BookingHelper::get_answer($shoppingcart,'phoneNumber');

        

        if($bank=="qris")
        {
            $data = [
                'tx_id' => $token,
                'amount' => $shoppingcart->due_now,
                'sender_name' => $first_name .' '. $last_name,
                'sender_phone' => $phone,
                'sender_notes' => null,
                'sender_email' => null,
                'email_active' => false,
                'card_sender' => 'qris_shopee'
            ];

            $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/qris/create';
            $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json'
          ];

          $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
          $response = $client->request('POST',$endpoint,
              ['json' => $data]
          );

          $data = $response->getBody()->getContents();
          $data = json_decode($data);

          
        }
        else
        {
            $data = [
                'amount' => $shoppingcart->due_now,
                'admin_fee' => 0,
                'sender_phone' => $phone,
                'sender_notes' => null,
                'sender_email' => null,
                'sender_name' => $first_name .' '. $last_name,
                'card_sender' => self::bankCode($bank),
                'device_id' => NULL,
                'payment_method' => 'VA',
                'email_active' => false
            ];

            $endpoint = self::oyCheckoutEndpoint() .'/b2x/v2/pay/enc/'. $token;

            $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json'
            ];

            $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
            $response = $client->request('PUT',$endpoint,
              ['json' => $data]
            );

            $data = $response->getBody()->getContents();
            $data = json_decode($data);

            
        }
        return $data;
  }

  public static function createSnap($shoppingcart)
  {
        $first_name = BookingHelper::get_answer($shoppingcart,'firstName');
        $last_name = BookingHelper::get_answer($shoppingcart,'lastName');
        $email = BookingHelper::get_answer($shoppingcart,'email');
        $phone = BookingHelper::get_answer($shoppingcart,'phoneNumber');

        $date_arr = array();
        foreach($shoppingcart->products as $product)
        {
            $date_arr[] = $product->date;
            
        }

        usort($date_arr, function($a, $b) {

            $dateTimestamp1 = strtotime($a);
            $dateTimestamp2 = strtotime($b);

            return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
        });

        $date = Carbon::parse($date_arr[0])->formatLocalized('%Y-%m-%d %H:%M:%S');

        $endpoint = self::oyApiEndpoint() ."/api/payment-checkout/create-v2";
        $headers = [
              'Cache-Control' => 'no-cache',
              'Content-Type' => 'application/json',
              'x-oy-username' => self::env_oyUsername(),
              'x-api-key' => self::env_oyApiKey(),
          ];

        $data = [
          'partner_tx_id' => $shoppingcart->confirmation_code,
          'sender_name' => $first_name .' '. $last_name,
          'amount' => $shoppingcart->due_now,
          'email' => null,
          'phone_number' => $phone,
          'username_display' => "VERTIKAL TRIP",
          'is_open' => false,
          'list_disabled_payment_methods' => "EWALLET,CREDIT_CARD",
          'expiration' => $date,
          'due_date' => $date,
        ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        
        $response = new \stdClass();
        $response->snaptoken = $data->payment_link_id;
        $response->link = $data->url;

        return $response;
  }

  
	
	
}
?>