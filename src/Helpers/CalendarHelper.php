<?php
namespace budisteikul\toursdk\Helpers;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;
use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartProduct;
use budisteikul\toursdk\Models\Calendar;

class CalendarHelper {

		public static function create_calendar($confirmation_code)
		{
			$shoppingcart = Shoppingcart::where('confirmation_code',$confirmation_code)->first();
			if($shoppingcart)
			{
				self::post($shoppingcart);
			}
		}

		public static function post($shoppingcart)
    	{
    		
    		foreach($shoppingcart->shoppingcart_products as $shoppingcart_product)
    		{
    			$content = BokunHelper::get_product($shoppingcart_product->product_id);
    			$people = self::count_person($shoppingcart_product->product_id,$shoppingcart_product->date);


    			$calendar = Calendar::where('product_id',$shoppingcart_product->product_id)->where('date',$shoppingcart_product->date)->first();
    			if($calendar)
    			{
    				$event_id = $calendar->google_calendar_id;
    				if($people==0)
    				{
    					Event::find($event_id)->delete();
    					Calendar::where('google_calendar_id',$event_id)->delete();
    					//print('people 0');
    				}
    				else if($calendar->people!=$people)
    				{
    					$event = Event::find($event_id);
    					$event->description = "A total of ". $people ." persons";
    					$event->save();
    					
						$calendar->google_calendar_id = $event_id;
						$calendar->product_id = $shoppingcart_product->product_id;
						$calendar->date = $shoppingcart_product->date;
						$calendar->people = $people;
						$calendar->save();

						//print('people not same');
    				}
    				else
    				{
    					//print($calendar->people);
    				}
    			}
    			else
    			{
    				if($people>0)
    				{
    					$event_id = self::post_to_calendar($shoppingcart_product->date,$content->title,"A total of ". $people ." persons",$content->durationHours);
    					//print('new');

    					$calendar = New Calendar();
						$calendar->google_calendar_id = $event_id;
						$calendar->product_id = $shoppingcart_product->product_id;
						$calendar->date = $shoppingcart_product->date;
						$calendar->people = $people;
						$calendar->save();
    				}
    			}


    			
    			
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

    	public static function post_to_calendar($string_date,$title,$description,$duration=1)
    	{
    		
    		$carbon = Carbon::createFromFormat('Y-m-d H:i:s', $string_date);
   			$event = new Event;
			$event->name = $title;
			$event->description = $description;
			$event->startDateTime = $carbon;
			$event->endDateTime = $carbon->addHours($duration);
			$newEvent = $event->save();
			return $newEvent->id;
    	}
	}
?>