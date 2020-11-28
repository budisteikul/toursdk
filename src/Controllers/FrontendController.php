<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use budisteikul\toursdk\Models\Category;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\Page;
use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\BookingHelper;

class FrontendController extends Controller
{
	public function __construct()
    {
		$this->bookingChannelUUID = env("BOKUN_BOOKING_CHANNEL");
		$this->currency = env("BOKUN_CURRENCY");
		$this->lang = env("BOKUN_LANG");
	}

	public function page($slug)
	{
		$page = Page::where('slug',$slug)->firstOrFail();
		return view('toursdk::frontend.page',['page'=>$page]);
	}

	public function booking($slug)
	{
		$sessionId = BookingHelper::shoppingcart_session();

		$product = Product::where('slug',$slug)->firstOrFail();
        $content = BokunHelper::get_product($product->bokun_id);
        
        $pickup = '';
        if($content->meetingType=='PICK_UP' || $content->meetingType=='MEET_ON_LOCATION_OR_PICK_UP')
        {
			$pickup = BokunHelper::get_product_pickup($content->id);
        }

        $availability = BokunHelper::get_availabilityactivity($content->id,1);
		$first = '[{"date":'. $availability[0]->date .',"localizedDate":"'. $availability[0]->localizedDate .'","availabilities":';
		$middle = json_encode($availability);
		$last = '}]';
		$firstavailability = $first.$middle.$last;

		$microtime = $availability[0]->date;
		$month = date("n",$microtime/1000);
		$year = date("Y",$microtime/1000);
		$embedded = "false";

        return view('toursdk::frontend.time-selector',[
        	'product'=>$product,
        	'content'=>$content,
        	'currency'=>$this->currency,
        	'lang'=>$this->lang,
        	'embedded'=>$embedded,
			'pickup'=>$pickup,
			'sessionId'=>$sessionId,
			'bookingChannelUUID'=>$this->bookingChannelUUID,
			'firstavailability'=>$firstavailability,
			'year'=>$year,
			'month'=>$month
        ]);
	}

	public function receipt($id,$sessionId)
	{
		$shoppingcart = Shoppingcart::where('id',$id)->where('session_id', $sessionId)
                        ->where('booking_status','CONFIRMED')->firstOrFail();
        return view('toursdk::frontend.receipt',['shoppingcart'=>$shoppingcart]);
	}

	public function checkout()
	{
		$sessionId = BookingHelper::shoppingcart_session();
		$shoppingcart = Shoppingcart::where('session_id', $sessionId)
                        ->where('booking_status','CART')->firstOrFail();
        
        if($shoppingcart->shoppingcart_products()->count()==0)
        {
            return redirect('/booking/shoppingcart/empty');
        }

        return view('toursdk::frontend.checkout',['shoppingcart'=>$shoppingcart]);
	}

    public function category($slug)
    {
        $category = Category::where('slug',$slug)->firstOrFail();
        return view('toursdk::frontend.category',['category'=>$category]);
    }

    public function product($slug)
    {
    	$sessionId = BookingHelper::shoppingcart_session();

        $product = Product::where('slug',$slug)->firstOrFail();
        $content = BokunHelper::get_product($product->bokun_id);
        
        $pickup = '';
        if($content->meetingType=='PICK_UP' || $content->meetingType=='MEET_ON_LOCATION_OR_PICK_UP')
        {
			$pickup = BokunHelper::get_product_pickup($content->id);
        }

        $availability = BokunHelper::get_availabilityactivity($content->id,1);
		$first = '[{"date":'. $availability[0]->date .',"localizedDate":"'. $availability[0]->localizedDate .'","availabilities":';
		$middle = json_encode($availability);
		$last = '}]';
		$firstavailability = $first.$middle.$last;

		$microtime = $availability[0]->date;
		$month = date("n",$microtime/1000);
		$year = date("Y",$microtime/1000);
		$embedded = "true";

        return view('toursdk::frontend.product',[
        	'product'=>$product,
        	'content'=>$content,
        	'currency'=>$this->currency,
        	'lang'=>$this->lang,
        	'embedded'=>$embedded,
			'pickup'=>$pickup,
			'sessionId'=>$sessionId,
			'bookingChannelUUID'=>$this->bookingChannelUUID,
			'firstavailability'=>$firstavailability,
			'year'=>$year,
			'month'=>$month
        ]);
    }
}
