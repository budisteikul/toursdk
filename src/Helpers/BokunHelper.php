<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class BokunHelper {
	
	public static function get_connect($path,$method = 'GET',$accept = 'application/json',$data="")
	{
		if(env("BOKUN_ENV")=="production")
		{
			$endpoint = "https://api.bokun.io";
		}
		else if(env("BOKUN_ENV")=="vertikaltrip")
		{
			$endpoint = "https://vertikaltrip.bokun.io";
		}
		else
		{
			$endpoint = "https://api.bokuntest.com";
		}

		//$endpoint = "https://vertikaltrip.bokun.io";

        $currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
        $query = '?currency='.$currency.'&lang='.$lang;
        $date = gmdate('Y-m-d H:i:s');
        $bokun_accesskey = env("BOKUN_ACCESSKEY");
        $bokun_secretkey = env("BOKUN_SECRETKEY");
		
		$string_signature = $date.$bokun_accesskey.$method.$path.$query;
        $sha1_signature =  hash_hmac("sha1",$string_signature, $bokun_secretkey, true);
        $base64_signature = base64_encode($sha1_signature);
    
        $headers = [
          'Accept' => $accept,
          'X-Bokun-AccessKey' => $bokun_accesskey,
          'X-Bokun-Date' => $date,
          'X-Bokun-Signature' => $base64_signature,
		  'X-Bokun-Channel' => env("BOKUN_BOOKING_CHANNEL"),
        ];
    
		$client = new \GuzzleHttp\Client(['headers' => $headers,'exceptions' => false]);
		if($method=="POST")
		{
			$response = $client->request('POST',$endpoint.$path.$query,
    			['json' => $data]
			);
		}
		else
		{
			$response = $client->request($method,$endpoint.$path.$query);
		}

		$statusCode = $response->getStatusCode(); 
		
		if(200 === $statusCode)
		{
			if($accept=='application/json')
			{
        		$contents = json_decode($response->getBody()->getContents());
			}
			else
			{
				$contents = $response->getBody()->getContents();
			}
			return $contents;
		}
		else if(400 === $statusCode)
		{
			$contents = json_decode($response->getBody()->getContents());
			if($contents->message == "No permission to access web services.");
			return redirect('/page/maintenence');
		}
		else
		{
			header("Location: /");
			exit();
		}
	}
	
	public static function get_product($activityId)
	{
		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->rememberForever('bokunProductbyid_'. $currency .'_'. $lang .'_'.$activityId, function() use ($activityId) {
    		return self::get_connect('/activity.json/'. $activityId);
		});
		return $value;
	}
	
	public static function get_productbyslug($slug)
	{
		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->rememberForever('bokunProductbyslug_'. $currency .'_'. $lang .'_'. $slug, function() use ($slug) {
    		return self::get_connect('/activity.json/slug/'. $slug);
		});
		return $value;
	}
	
	public static function get_product_pickup($activityId)
	{
		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->rememberForever('bokunProductpickup_'. $currency .'_'. $lang .'_'. $activityId, function() use ($activityId) {
    		return self::get_connect('/activity.json/'. $activityId .'/pickup-places');
		});
		return $value;
	}
	
	public static function get_product_list_byid($id)
	{
		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->rememberForever('bokunProductlistbyid_'. $currency .'_'. $lang .'_'. $id, function() use ($id) {
    		return self::get_connect('/product-list.json/'. $id);
		});
		return $value;
	}
	
	public static function get_product_list()
	{

		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->rememberForever('bokunProductlist_'. $currency .'_'. $lang, function() {
    		return self::get_connect('/product-list.json/list');
		});
		return $value;
	}
	
	public static function get_activeids()
	{
		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->rememberForever('bokunActiveids_'. $currency .'_'. $lang, function() {
    		return self::get_connect('/activity.json/active-ids');
		});
		return $value;
	}
	
	public static function get_country()
	{
		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->rememberForever('bokunCountry_'. $currency .'_'. $lang, function() {
    		return self::get_connect('/country.json/findAll');
		});
		return $value;
	}
	
	//=====================================================================================

	public static function get_calendar($activityId,$year,$month)
	{
		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->remember('bokunCalendar_'.$activityId .'_'.$year .'_'.$month .'_'. $currency .'_'. $lang,7200, function() use ($activityId,$year,$month) {
    		return self::get_connect('/snippets/activity/'.$activityId.'/calendar/json/'.$year.'/'.$month);
		});
		return $value;
	}

	public static function get_availabilities($id,$start,$end)
	{
		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->remember('bokunAvailability_'. $id .'_'. $start .'_'. $end .'_'. $lang .'_'. $currency,7200, function() use ($id,$start,$end,$lang,$currency)
		{
    		return self::get_connect('/activity.json/'.$id.'/availabilities?start='.$start.'&end='.$end.'&lang='. $lang .'&currency='.$currency.'&includeSoldOut=false');
		});
    	return $value;
	}

	public static function get_currency()
	{
		$currency = env("BOKUN_CURRENCY");
        $lang = env("BOKUN_LANG");
		$value = Cache::store('database')->remember('bokunCurrency_'. $currency .'_'. $lang,7200, function()
		{
    		return self::get_connect('/currency.json/findAll');
		});
    	return $value;
	}
	
	public static function get_invoice($data)
	{
		return self::get_connect('/snippets/activity/invoice-preview','POST','application/json',$data);
	}

	//=====================================================================================

	
	public static function get_questionshoppingcart($id)
	{
		return self::get_connect('/question.json/shopping-cart/'.$id);
	}
	
	public static function get_questionbooking($id)
	{
		return self::get_connect('/question.json/activity-booking/'.$id);
	}

	public static function get_checkout($sessionId)
	{
		return self::get_connect('/checkout.json/options/shopping-cart/'. $sessionId);
	}
	
	public static function get_shoppingcart($sessionId)
	{
		return self::get_connect('/shopping-cart.json/session/'. $sessionId);
	}
	
	public static function get_ticket($confirmationCode)
	{
		return self::get_connect('/booking.json/activity-booking/'.$confirmationCode.'/ticket','GET','application/pdf');
	}
	
	public static function get_invoicepdf($id)
	{
		return self::get_connect('/booking.json/'. $id .'/summary','GET','application/pdf');
	}
	
	public static function get_productbooking($id)
	{
		return self::get_connect('/booking.json/activity-booking/'.$id);
	}
	
	public static function get_removeshoppingcart($sessionId,$id)
	{
		return self::get_connect('/shopping-cart.json/session/'.$sessionId.'/remove-activity/'.$id);
	}
	
	public static function get_removepromocode($sessionId)
	{
		return self::get_connect('/cart.json/'.$sessionId.'/remove-promo-code');
	}
	
	public static function get_applypromocode($sessionId,$id)
	{
		$id = strtolower($id);
		return self::get_connect('/cart.json/'.$sessionId.'/apply-promo-code/'.$id);
	}
	
	public static function get_removeactivity($sessionId,$id)
	{
		return self::get_connect('/shopping-cart.json/session/'.$sessionId.'/remove-activity/'. $id);
	}
	
	public static function get_availabilityactivity($id,$max)
	{
		return self::get_connect('/activity.json/'.$id.'/upcoming-availabilities/'.$max);
	}
	
	public static function get_addshoppingcart($sessionId,$data)
	{
		return self::get_connect('/shopping-cart.json/session/'. $sessionId .'/activity','POST','application/json',$data);
	}
}
?>