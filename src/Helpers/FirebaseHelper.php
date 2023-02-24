<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\ContentHelper;
use budisteikul\toursdk\Models\Shoppingcart;

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
    
	public static function delete($shoppingcart)
	{
            self::connect("receipt/".$shoppingcart->session_id .'/'. $shoppingcart->confirmation_code,"","DELETE");
	}

	public static function upload($shoppingcart)
  	{
            if(!BookingHelper::have_payment($shoppingcart))
            {
                return "";
            }
            $dataObj = ContentHelper::view_receipt($shoppingcart); 
            $data = array(
                'receipt' => $dataObj,
                'message' => 'success'
            );
            self::connect('receipt/'.$shoppingcart->session_id ."/". $shoppingcart->confirmation_code,$data,"PUT");
  	}

    public static function upload_payment($status,$reference_id,$session_id=null,$ewallet=null,$redirect_url=null)
    {
            $data = array(
                'status' => $status,
                'session_id' => $session_id,
                'ewallet' => $ewallet,
                'redirect_url' => $redirect_url
            );
            self::connect('payment/ewallet/'.$reference_id,$data,"PUT");
    }

    public static function read_payment($reference_id)
    {
            return self::connect('payment/ewallet/'.$reference_id,"","GET");
    }
}
?>