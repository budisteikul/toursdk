<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;
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

  public static function env_appName()
  {
        return env("APP_NAME");
  }

  public static function oyApiEndpoint()
  {
        if(env('OY_ENV')=="production")
        {
            $endpoint = "https://partner.oyindonesia.com";
        }
        else
        {
            $endpoint = "https://api-stg.oyindonesia.com";
        }
        return $endpoint;
  }

  public static function bankCode($bank_name)
  {
    if($bank_name=="") $bank_name = "btpn";
    switch($bank_name)
    {
      case "btpn":
        $bank_code = "213";
      break;
      case "bri":
        $bank_code = "002";
      break;
      case "cimbniaga":
        $bank_code = "022";
      break;
       case "mandiri":
        $bank_code = "008";
      break;
      default :
        $bank_code = "213";
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
          'note' => self::env_appName(),
          'partner_trx_id' => $disbursement->id,
        ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        
        return $data;
       
  }

  public static function createPaymentLink($shoppingcart)
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
          'amount' => 10000,
          'email' => $email,
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
        
        
        return $data;
  }

  public static function createVA($shoppingcart,$bank_name="")
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

        $date1 = Carbon::now();
        $date2 = Carbon::parse($date_arr[0]);
        $mins  = $date2->diffInMinutes($date1, true);
        if($mins<=15) $mins = 15;

        $date_now = Carbon::parse($date1)->formatLocalized('%Y-%m-%d %H:%M:%S +0700');

        $endpoint = self::oyApiEndpoint() ."/api/generate-static-va";
        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json',
              'x-oy-username' => self::env_oyUsername(),
              'x-api-key' => self::env_oyApiKey(),
          ];

        $data = [
          'partner_user_id' => $shoppingcart->confirmation_code,
          'bank_code' => self::bankCode($bank_name),
          'amount' => $shoppingcart->total,
          'is_open' => false,
          'is_single_use' => true,
          'is_lifetime' => false,
          'expiration_time' => $mins,
          'username_display' => $first_name,
          'email' => $email,
          'trx_expiration_time' => $mins,
          'partner_trx_id' => $shoppingcart->confirmation_code,
          'trx_counter' => 1,
        ];

        
        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        return $data;

  }
	
	
}
?>