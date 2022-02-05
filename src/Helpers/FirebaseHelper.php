<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\BookingHelper;

class FirebaseHelper {

    public static function env_firebaseDatabaseUrl()
    {
        return env("FIREBASE_DATABASE_URL");
    }

    public static function env_firebaseDatabaseSecret()
    {
        return env("FIREBASE_DATABASE_SECRET");
    }

	public static function delete($shoppingcart,$index="")
	{
        if($index=="") $index = "receipt";

        if($index=="receipt")
        {
            self::connect("receipt/".$shoppingcart->session_id .'/'. $shoppingcart->id,"","DELETE");
        }
	}

    public static function connect($path,$data="",$method="PUT")
    {
        if($method=="PUT")
        {
            $endpoint = "https://". self::env_firebaseDatabaseUrl() ."/". $path .".json?auth=". self::env_firebaseDatabaseSecret();
            $client = new \GuzzleHttp\Client(['http_errors' => false]);
            $response = $client->request('PUT',$endpoint,
                ['body' => json_encode($data)]
            );
            $data = $response->getBody()->getContents();
            $data = json_decode($data,true);
        }

        if($method=="DELETE")
        {
            $endpoint = "https://". self::env_firebaseDatabaseUrl() ."/".$path .".json?auth=". self::env_firebaseDatabaseSecret();
            $client = new \GuzzleHttp\Client(['http_errors' => false]);
            $response = $client->request('DELETE',$endpoint);

            $data = $response->getBody()->getContents();
            $data = json_decode($data,true);
        }
            
    }

	public static function upload($shoppingcart,$index="")
  	{
        if($index=="") $index = "receipt";

        if($index=="receipt")
        {
            if(!BookingHelper::have_payment($shoppingcart))
            {
                return "";
            }

            $dataObj = BookingHelper::view_receipt($shoppingcart); 

            $data = array(
                'receipt' => $dataObj,
                'message' => 'success'
            );
            
            self::connect('receipt/'.$shoppingcart->session_id ."/". $shoppingcart->id,$data,"PUT");
            return "";
        }
  		
  	}
}
?>