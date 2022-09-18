<?php
namespace budisteikul\toursdk\Helpers;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;
use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartProduct;
use budisteikul\toursdk\Models\Calendar;

class EventHelper {
		public static function test()
    	{
    		$shoppingcarts = Shoppingcart::where('confirmation_code','BR-933666404')->firstOrFail();
    		foreach($shoppingcarts->shoppingcart_products as $shoppingcart_product)
    		{
    			$content = BokunHelper::get_product($shoppingcart_product->product_id);
    			$person = self::count_person($shoppingcart_product->product_id,$shoppingcart_product->date);
    			//print_r($shoppingcart_product->date);
    			//print_r($content->durationHours);
    			//print_r($content->title);
    			//print_r($person);
    			print_r($person);
    		}
    	}

    	public static function count_person($product_id,$date)
    	{
    		$persons = 0;
    		$products = ShoppingcartProduct::with('shoppingcart')->WhereHas('shoppingcart', function($query) {
    			$query->where('booking_status','CONFIRMED');
			})
			->where('product_id',$product_id)->where('date',$date)->get();
    		foreach($products as $product)
    		{
    			foreach($product->shoppingcart_product_details as $detail)
    			{
    				$persons += $detail->people;
    			}
    			
    		}
    		return $persons;
    	}

    	public static function test2($product_id,$date,$title,$persons)
    	{
    		$string_date = '2022-09-18 18:30:00';
    		$title = "Yogyakarta Night Walking and Food Tours";
    		$description = "A total of 7 persons";

    		$carbon = Carbon::createFromFormat('Y-m-d H:i:s', $string_date);

   			$event = new Event;

			$event->name = $title;
			$event->description = $description;
			$event->startDateTime = $carbon;
			$event->endDateTime = $carbon->addMinutes(1);


			$event->save();
    	}
	}
?>