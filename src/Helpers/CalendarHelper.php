<?php

namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Calendar;
use budisteikul\toursdk\Helpers\BookingHelper;
use Spatie\GoogleCalendar\Event;

class CalendarHelper {


    public static function update_calendar($shoppingcart,$action)
    {
        $endpoint = env("GOOGLE_CALENDAR_URL");
        $headers = [
              'X-Foo' => 'Bar',
          ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['form_params' => [
            'confirmation_code' => $shoppingcart->confirmation_code,
            'action' => $action
          ]]
        );
    }

	public static function create_event($shoppingcart)
    {

    	if(Calendar::where('shoppingcart_id',$shoppingcart->id)->exists())
    	{
    		foreach($shoppingcart->shoppingcart_products as $shoppingcart_product)
    		{
    			if($shoppingcart_product->date!=null)
    			{
    				$people = 0;
    				foreach($shoppingcart_product->shoppingcart_product_details as $shoppingcart_product_detail)
    				{
    					$people += $shoppingcart_product_detail->people;
    				}
    				
    				$event = Event::find($shoppingcart->calendar->google_calendar_id);
					$event->name = $people .' person ('. $shoppingcart->confirmation_code .')';
					$event->save();
    			}
    		
    		}
    	}
    	else
    	{
    		foreach($shoppingcart->shoppingcart_products as $shoppingcart_product)
    		{
    			if($shoppingcart_product->date!=null)
    			{
    				$people = 0;
    				foreach($shoppingcart_product->shoppingcart_product_details as $shoppingcart_product_detail)
    				{
    					$people += $shoppingcart_product_detail->people;
    				}
    		
    				$event = new Event;
        			$event->name = $people .' person ('. $shoppingcart->confirmation_code .')';
        			$event->startDateTime = \Carbon\Carbon::parse($shoppingcart_product->date);
        			$event->endDateTime = \Carbon\Carbon::parse($shoppingcart_product->date)->addHour(3);
        			$event->addAttendee([
            			'email' => BookingHelper::set_maskingEmail($shoppingcart),
        			]);
        			$newEvent = $event->save();

        			$calendar = New Calendar();
        			$calendar->shoppingcart_id = $shoppingcart->id;
        			$calendar->google_calendar_id = $newEvent->id;
        			$calendar->save();
    			}
    		
    		}
    	}

    }


    public static function delete_event($shoppingcart)
    {
    	if(Calendar::where('shoppingcart_id',$shoppingcart->id)->exists())
    	{
    		$event = Event::find($shoppingcart->calendar->google_calendar_id);
    		$event->delete();

    		$shoppingcart->calendar->delete();
    	}
    }

}

?>