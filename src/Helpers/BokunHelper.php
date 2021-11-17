<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class BokunHelper {


    public static function get_bokunBookingChannel()
    {
   		return $bookingChannel = env("BOKUN_BOOKING_CHANNEL");
    }
    public static function get_bokunCurrency()
    {
   		return $currency = env("BOKUN_CURRENCY");
    }
    public static function get_bokunLang()
    {
   		return $lang = env("BOKUN_LANG");
    }
    public static function get_bokunEnv()
    {
   		return $env = env("BOKUN_ENV");
    }
    public static function get_bokunAccessKey()
    {
   		return $env = env("BOKUN_ACCESS_KEY");
    }
    public static function get_bokunSecretKey()
    {
   		return $env = env("BOKUN_SECRET_KEY");
    }

    public static function bokunAPI_connect($path, $method = 'GET', $data = "")
    {
    		if(self::get_bokunEnv()=="production")
			{
				$endpoint = "https://api.bokun.io";
			}
			else
			{
				$endpoint = "https://api.bokuntest.com";
			}

			$currency = self::get_bokunCurrency();
        	$lang = self::get_bokunLang();
        	$param = '?currency='.$currency.'&lang='.$lang;
        	$date = gmdate('Y-m-d H:i:s');
        	$bokun_accesskey = self::get_bokunAccessKey();
        	$bokun_secretkey = self::get_bokunSecretKey();

			$string_signature = $date.$bokun_accesskey.$method.$path.$param;
        	$sha1_signature =  hash_hmac("sha1",$string_signature, $bokun_secretkey, true);
        	$base64_signature = base64_encode($sha1_signature);

        	$headers = [
          		'Accept' => 'application/json',
          		'X-Bokun-AccessKey' => $bokun_accesskey,
          		'X-Bokun-Date' => $date,
          		'X-Bokun-Signature' => $base64_signature,
		  		'X-Bokun-Channel' => self::get_bokunBookingChannel(),
        	];

        	$client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);

        	if($method=="POST")
			{
				$response = $client->request($method,$endpoint.$path.$param,
    			[	
    				'json' => $data
    			]);
			}
			else
			{
				$response = $client->request($method,$endpoint.$path.$param);
			}

			$contents = $response->getBody()->getContents();
			return $contents;
    }

    

    public static function bokunWidget_connect($path, $method = 'GET', $data = "")
	{

			if(self::get_bokunEnv()=="production")
			{
				$endpoint = "https://widgets.bokun.io";
			}
			else
			{
				$endpoint = "https://widgets.bokuntest.com";
			}
			
			$headers = [
		  		'x-bokun-channel' => self::get_bokunBookingChannel(),
		  		'content-type' => 'application/json',
        	];

      		$client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);

      		if($method=="POST")
			{
				$response = $client->request($method,$endpoint.$path,
    			[	
    				'json' => $data
    			]);
			}
			else
			{
				
				$response = $client->request($method,$endpoint.$path);

			}

			$contents = $response->getBody()->getContents();
			return $contents;
	}

    public static function get_confirmBooking($sessionId)
	{
        $currency = self::get_bokunCurrency();
        $lang = self::get_bokunLang();
        $bookingChannel = self::get_bokunBookingChannel();

        $data = '{"checkoutOption":"CUSTOMER_NO_PAYMENT"}';
        $data = json_decode($data);
        $value = self::bokunWidget_connect('/widgets/'.$bookingChannel.'/checkout?sessionId='.$sessionId.'&lang='.$lang.'&currency='.$currency,'POST', $data);
        $value = json_decode($value);
        return $value->booking->confirmationCode;
	}

	public static function get_cancelProductBooking($product_confirmation_code)
    {
    	$data = '{"note": "test","notify": false,"refund": false,"refundAmount": 0,"remainInvoiced": false}';
        $data = json_decode($data);
        $value = self::bokunAPI_connect('/booking.json/cancel-product-booking/'.$product_confirmation_code,'POST', $data);
        $value = json_decode($value);
        return $value;
    }

    public static function get_currency()
	{
		
        $currency = self::get_bokunCurrency();
        $lang = self::get_bokunLang();
        $bookingChannel = self::get_bokunBookingChannel();

        $value = Cache::remember('_bokunCurrency_'. $currency .'_'. $lang,7200, function() use ($currency,$lang,$bookingChannel)
		{
    		return self::bokunWidget_connect('/widgets/'.$bookingChannel.'/config/conversionRate?lang='.$lang.'&currency='.$currency);
		});
		$value = json_decode($value);
		return number_format($value->paymentCurrencyRateToDollar->conversionRate,5,'.',',');
	}

	public static function get_removepromocode($sessionId)
	{
		$currency = self::get_bokunCurrency();
        $lang = self::get_bokunLang();
        $bookingChannel = self::get_bokunBookingChannel();

        $value = self::bokunWidget_connect('/widgets/'. $bookingChannel .'/checkout/promoCode?lang='. $lang .'&currency='.$currency.'&sessionId='. $sessionId,'DELETE');
		$value = json_decode($value);
		return $value;
	}

	public static function get_applypromocode($sessionId,$id)
	{
		$currency = self::get_bokunCurrency();
        $lang = self::get_bokunLang();
        $bookingChannel = self::get_bokunBookingChannel();

        $id = strtolower($id);
        $value = self::bokunWidget_connect('/widgets/'. $bookingChannel .'/checkout/promoCode/'. $id .'?lang='. $lang .'&currency='.$currency.'&sessionId='. $sessionId,'POST');
		$value = json_decode($value);
		return $value;
	}

	public static function get_removeactivity($sessionId,$id)
	{
		$currency = self::get_bokunCurrency();
        $lang = self::get_bokunLang();
        $bookingChannel = self::get_bokunBookingChannel();

		$value = self::bokunWidget_connect('/widgets/'. $bookingChannel .'/shoppingCart/activity/remove/'. $id .'?lang='. $lang .'&currency='.$currency.'&sessionId='. $sessionId,'DELETE');
		$value = json_decode($value);
		
		return $value;
	}

	

	public static function get_questionshoppingcart($id)
	{
		$currency = self::get_bokunCurrency();
		$lang = self::get_bokunLang();
		$bookingChannel = self::get_bokunBookingChannel();

		$value = self::bokunWidget_connect('/widgets/'.$bookingChannel.'/checkout/cartBookingOptions?lang='.$lang.'&currency='.$currency.'&sessionId='. $id);
		$value = json_decode($value);
		return $value;
	}

	public static function get_addshoppingcart($sessionId,$data)
	{
		$currency = self::get_bokunCurrency();
		$lang = self::get_bokunLang();
		$bookingChannel = self::get_bokunBookingChannel();

		$value = self::bokunWidget_connect('/widgets/'. $bookingChannel .'/shoppingCart/activity/add?lang='. $lang .'&currency='.$currency.'&sessionId='. $sessionId,'POST',$data);
		$value = json_decode($value);
		return $value;
	}

	public static function get_invoice($data)
	{
		$currency = self::get_bokunCurrency();
        $lang = self::get_bokunLang();
        
		$value = json_decode(self::bokunWidget_connect('/snippets/activity/invoice-preview?currency='.$currency.'&lang='.$lang,'POST',$data));
		return $value;
	}

	public static function get_calendar_new($activityId,$year="",$month="")
	{
		
		$currency = self::get_bokunCurrency();
        $lang = self::get_bokunLang();
        $bookingChannel = self::get_bokunBookingChannel();

        if($year=="") $year = -1;
        if($month=="") $month = -1;
        
        
        $data = '{"guidedLanguages":[],"pricingCategories":[]}';
        $data = json_decode($data);

		$value = self::bokunWidget_connect('/widgets/'.$bookingChannel.'/activity/'.$activityId.'/'.$year.'/'.$month.'?lang='.$lang.'&currency='.$currency,'POST',$data);
		
		$value = json_decode($value);
		return $value->calendar;
	}

	public static function get_calendar($activityId,$year="",$month="")
	{
		$currency = self::get_bokunCurrency();
        $lang = self::get_bokunLang();

        if($year=="") $year = date('Y');
        if($month=="") $month = date('m');
        
		$value = self::bokunWidget_connect('/snippets/activity/'.$activityId.'/calendar/json/'.$year.'/'.$month .'?lang='.$lang.'&currency='.$currency);
		
		$value = json_decode($value);
		return $value;
	}


	public static function get_firstAvailability($activityId,$year,$month)
	{
		$availability = self::get_calendar($activityId,$year,$month);
		$value[] = $availability->firstAvailableDay->availabilities[0]->activityAvailability;
		$dataObj[] = [
			'date' => $value[0]->date,
			'localizedDate' => $value[0]->localizedDate,
			'availabilities' => $value
		];
		return $dataObj;
	}


	public static function get_product($activityId)
	{
		$currency = self::get_bokunCurrency();
		$lang = self::get_bokunLang();
		$bookingChannel = self::get_bokunBookingChannel();
		$value = Cache::rememberForever('_bokunProductById_'. $currency .'_'. $lang .'_'.$activityId, function() use ($activityId,$currency,$lang,$bookingChannel) {
    		return self::bokunWidget_connect('/widgets/'.$bookingChannel.'/activity/'.$activityId.'?lang='.$lang.'&currency='.$currency);
		});
		$value = json_decode($value);
		return $value->activity;
	}

	public static function get_product_pickup($activityId)
	{
		$currency = self::get_bokunCurrency();
        $lang = self::get_bokunLang();
        $bookingChannel = self::get_bokunBookingChannel();

		$value = Cache::remember('_bokunProductPickup_'. $currency .'_'. $lang .'_'. $activityId,7200, function() use ($activityId,$lang,$bookingChannel) {
    		return self::bokunWidget_connect('/widgets/'.$bookingChannel.'/activity/'.$activityId.'/pickupPlaces?selectedLang='.$lang);
		});
		$value = json_decode($value);
		return $value;
	}
	
}
?>