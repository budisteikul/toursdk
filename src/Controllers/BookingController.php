<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\PaypalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;

class BookingController extends Controller
{
    public function __construct()
    {
        if(!Session::has('sessionId')){
            $sessionId = Uuid::uuid4()->toString();
            Session::put('sessionId',$sessionId);
        }

        $this->bookingChannelUUID = env("BOKUN_BOOKING_CHANNEL");
        $this->currency = env("BOKUN_CURRENCY");
        $this->lang = env("BOKUN_LANG");
    }

    public function checkout(Request $request)
    {
        
        if(!Session::has('sessionId')){
            return redirect(route('route_toursdk_booking.index'));
        }
        
        $sessionId = Session::get('sessionId');
        $shoppingcart = Shoppingcart::where('sessionId', $sessionId)
                        ->where('booking_status','CART')->first();
        
        if(!isset($shoppingcart))
        {
            return redirect(route('route_toursdk_booking.index'));
        }
        
        if($shoppingcart->shoppingcart_products()->count()==0)
        {
            return redirect(route('route_toursdk_booking.index'));
        }
        
        $channels = Channel::get();
        return view('toursdk::booking.checkout')
                ->with([
                        'shoppingcart'=>$shoppingcart,
                        'channels'=>$channels
                    ]);
    }

    public function shoppingcart(Request $request)
    {
        $id = $request->input('sessionId');
        BookingHelper::get_shoppingcart($id,"insert");
        //return redirect(route('route_toursdk_booking.index')."/checkout");
    }

    public function calendar(Request $request)
    {
        $id = $request->input('activityId');
        $contents = BokunHelper::get_product($id);
        $pickup = '';
        if($contents->meetingType=='PICK_UP' || $contents->meetingType=='MEET_ON_LOCATION_OR_PICK_UP')
        {
            $pickup = BokunHelper::get_product_pickup($id);
        }
        
        if(Session::has('sessionId')){
            $sessionId = Session::get('sessionId');
        }else{
            $sessionId = Uuid::uuid4()->toString();
            Session::put('sessionId',$sessionId);
        }
        $bookingChannelUUID = $this->bookingChannelUUID;
        
        $availability = BokunHelper::get_availabilityactivity($contents->id,1);
        $first = '[{"date":'. $availability[0]->date .',"localizedDate":"'. $availability[0]->localizedDate .'","availabilities":';
        $middle = json_encode($availability);
        $last = '}]';
        $firstavailability = $first.$middle.$last;
        
        $microtime = $availability[0]->date;
        $month = date("n",$microtime/1000);
        $year = date("Y",$microtime/1000);
        $embedded = "false";

        $currency = $this->currency;
        $lang = $this->lang;

        return view('toursdk::booking.calendar')->with(['currency'=>$currency,'lang'=>$lang,'embedded'=>$embedded,'contents'=>$contents,'pickup'=>$pickup,'sessionId'=>$sessionId,'bookingChannelUUID'=>$bookingChannelUUID,'firstavailability'=>$firstavailability,'year'=>$year,'month'=>$month]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
