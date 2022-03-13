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

    public static function env_firebaseDynamicLinkApiKey()
    {
        return env("FIREBASE_DYNAMIC_LINK_API_KEY");
    }

    public static function env_firebaseDynamicLinkDomainUri()
    {
        return env("FIREBASE_DYNAMIC_LINK_DOMAIN_URI");
    }

    public static function createDynamicLink($link,$app="gopay")
    {
        if($app=="gopay") {
            $androidPackageName = 'com.gojek.app';
            $androidFallbackLink = 'https://play.google.com/store/apps/details?id=com.gojek.app';
            $iosFallbackLink = 'https://apps.apple.com/id/app/gojek/id944875099';
            $iosBundleId = 'com.go-jek.ios';
        }

        $endpoint = "https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=". self::env_firebaseDynamicLinkApiKey();
        $headers = [
              'Accept' => 'application/jsons',
              'Content-Type' => 'application/json'
          ];

        $data = [
          'dynamicLinkInfo' => [
            'domainUriPrefix' => self::env_firebaseDynamicLinkDomainUri(),
            'link' => $link,
            'androidInfo' => [
                'androidFallbackLink' => $androidFallbackLink,
                'androidPackageName' => $androidPackageName
            ],
            'iosInfo' => [
                'iosFallbackLink' => $iosFallbackLink,
                'iosBundleId' => $iosBundleId
            ]
          ]
        ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          [
            'json' => $data
          ]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data);
        
        return $data->shortLink;
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
    
	public static function delete($shoppingcart,$index="")
	{
        if($index=="") $index = "receipt";

        if($index=="receipt")
        {
            self::connect("receipt/".$shoppingcart->session_id .'/'. $shoppingcart->confirmation_code,"","DELETE");
            
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

            $dataObj = ContentHelper::view_receipt($shoppingcart); 

            $data = array(
                'receipt' => $dataObj,
                'message' => 'success'
            );
            
            self::connect('receipt/'.$shoppingcart->session_id ."/". $shoppingcart->confirmation_code,$data,"PUT");
            
            return "";
        }
  		
  	}
}
?>