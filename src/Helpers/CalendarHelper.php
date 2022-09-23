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
			foreach($shoppingcart->shoppingcart_products as $shoppingcart_product)
    		{
    			$content = BokunHelper::get_product($shoppingcart_product->product_id);
    			$people = self::count_person($shoppingcart_product->product_id,$shoppingcart_product->date);


    			$calendar = Calendar::where('product_id',$shoppingcart_product->product_id)->where('date',$shoppingcart_product->date)->first();
    			if($calendar)
    			{
    					$event_id = $calendar->google_calendar_id;
    					$event = Event::find($event_id);
    					$event->description = "A total of ". $people ." persons";
    					$event->save();
    					
						$calendar->google_calendar_id = $event_id;
						$calendar->product_id = $shoppingcart_product->product_id;
						$calendar->date = $shoppingcart_product->date;
						$calendar->people = $people;
						$calendar->save();
    			}
    			else
    			{
    				if($people>0)
    				{
    					$carbon = Carbon::createFromFormat('Y-m-d H:i:s', $shoppingcart_product->date);
   						$event = new Event;
						$event->name = $content->title;
						$event->description = "A total of ". $people ." persons";
						$event->startDateTime = $carbon;
						$event->endDateTime = $carbon->addHours($content->durationHours);
						$newEvent = $event->save();
						$event_id = $newEvent->id;

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
		}

		public static function update_calendar($confirmation_code)
    	{
    		$shoppingcart = Shoppingcart::where('confirmation_code',$confirmation_code)->first();
			if($shoppingcart)
			{
				foreach($shoppingcart->shoppingcart_products as $shoppingcart_product)
    			{
    				$calendar = Calendar::where('product_id',$shoppingcart_product->product_id)->where('date',$shoppingcart_product->date)->first();
    				if($calendar)
    				{
    					foreach($shoppingcart_product->shoppingcart_product_details as $shoppingcart_product_detail)
    					{
    						$people = $calendar->people - $shoppingcart_product_detail->people;
    						if($people>0)
    						{
    							$event = Event::find($calendar->google_calendar_id);
    							$event->description = "A total of ". $people ." persons";
								$event->save();

								$calendar->people = $people;
								$calendar->save();
    						}
    						else
    						{
    							$event = Event::find($calendar->google_calendar_id);
    							$event->delete();

    							$calendar->delete();
    						}
    					}
    			
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

	}
?>