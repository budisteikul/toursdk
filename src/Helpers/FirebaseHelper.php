<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\PaymentHelper;
use budisteikul\toursdk\Helpers\ContentHelper;
use budisteikul\toursdk\Models\Shoppingcart;
use Illuminate\Support\Facades\Cache;

class FirebaseHelper {

    public static function env_firebaseDatabaseUrl()
    {
        return env("FIREBASE_DATABASE_URL");
    }

    public static function env_firebaseDatabaseSecret()
    {
        return env("FIREBASE_DATABASE_SECRET");
    }

    

    public static function connect($path,$data="",$method="PUT")
    {
        $response = null;

        if($method=="PUT")
        {
            $endpoint = "https://". self::env_firebaseDatabaseUrl() ."/". $path .".json?auth=". self::env_firebaseDatabaseSecret();
            $client = new \GuzzleHttp\Client(['http_errors' => false]);
            $response = $client->request('PUT',$endpoint,
                ['body' => json_encode($data)]
            );
            $data = $response->getBody()->getContents();
            $response = json_decode($data);
        }

        if($method=="DELETE")
        {
            $endpoint = "https://". self::env_firebaseDatabaseUrl() ."/".$path .".json?auth=". self::env_firebaseDatabaseSecret();
            $client = new \GuzzleHttp\Client(['http_errors' => false]);
            $response = $client->request('DELETE',$endpoint);

            $data = $response->getBody()->getContents();
            $response = json_decode($data);
        }

        if($method=="GET")
        {
            $endpoint = "https://". self::env_firebaseDatabaseUrl() ."/".$path .".json?auth=". self::env_firebaseDatabaseSecret();
            $client = new \GuzzleHttp\Client(['http_errors' => false]);
            $response = $client->request('GET',$endpoint);

            $data = $response->getBody()->getContents();
            $response = json_decode($data);

        }
            
        return $response;
    }
    
    public static function shoppingcart($sessionId)
    {
            $shoppingcart = Cache::get('_'. $sessionId);
            $dataShoppingcart = ContentHelper::view_shoppingcart($shoppingcart);

            $dataFirebase = array(
                'shoppingcarts' => $dataShoppingcart,
                'api_url' => env('APP_API_URL'),
                'payment_enable' => config('site.payment_enable'),
                'payment_default' => config('site.payment_default'),
                'cancellationPolicy' => 'I agree with the <a class="text-theme" href="/page/terms-and-conditions" target="_blank">terms and conditions</a>.',
                'message' => 'success'
            );
            self::connect('shoppingcart/'.$sessionId,$dataFirebase,"PUT");
    }

    public static function receipt($shoppingcart)
    {
        if(!PaymentHelper::have_payment($shoppingcart))
        {
            return "";
        }
        $dataObj = ContentHelper::view_receipt($shoppingcart); 
        $data = array(
                    'receipt' => $dataObj,
                    'api_url' => env('APP_API_URL'),
                    'message' => 'success'
                );
        self::connect('receipt/'.$shoppingcart->session_id ."/". $shoppingcart->confirmation_code,$data,"PUT");
    }

    public static function payment($status,$session_id,$redirect_url=null)
    {
            $data = array(
                'status' => $status,
                'redirect_url' => $redirect_url
            );
            self::connect('payment/'.$session_id,$data,"PUT");
    }

    public static function read_payment($session_id)
    {
            return self::connect('payment/'.$session_id,"","GET");
    }
}
?>