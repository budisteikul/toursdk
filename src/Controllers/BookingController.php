<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\PaypalHelper;
use budisteikul\toursdk\Models\Channel;
use budisteikul\toursdk\Models\Product;

use budisteikul\toursdk\DataTables\BookingDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use budisteikul\toursdk\Mail\BookingConfirmedMail;


use DB;
use budisteikul\toursdk\Models\Review;
use budisteikul\toursdk\Models\ShoppingcartProduct;
use budisteikul\toursdk\Models\ShoppingcartRate;
use budisteikul\toursdk\Models\ShoppingcartQuestion;
use budisteikul\toursdk\Models\ShoppingcartQuestionOption;
use budisteikul\toursdk\Models\ShoppingcartPayment;

class BookingController extends Controller
{
    public function __construct()
    {
        
        $this->bookingChannelUUID = env("BOKUN_BOOKING_CHANNEL");
        $this->currency = env("BOKUN_CURRENCY");
        $this->lang = env("BOKUN_LANG");
    }
    

    public function checkout(Request $request)
    {
        $sessionId = BookingHelper::shoppingcart_session();
        
        $shoppingcart = Shoppingcart::where('session_id', $sessionId)
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

    public function calendar(Request $request)
    {
		$validator = Validator::make($request->all(), [
                    'activityId' => 'required|integer'
        ]);
                
        if ($validator->fails()) {
                return redirect(route('route_toursdk_product.index'));
        }
			
        $sessionId = BookingHelper::shoppingcart_session();

        $id = $request->input('activityId');
        $contents = BokunHelper::get_product($id);
        $bookingChannelUUID = $this->bookingChannelUUID;
        $currency = $this->currency;
        $lang = $this->lang;
        
		

        $pickup = '';
        if($contents->meetingType=='PICK_UP' || $contents->meetingType=='MEET_ON_LOCATION_OR_PICK_UP')
        {
            $pickup = BokunHelper::get_product_pickup($id);
        }

        $availability = BokunHelper::get_availabilityactivity($contents->id,1);
        $first = '[{"date":'. $availability[0]->date .',"localizedDate":"'. $availability[0]->localizedDate .'","availabilities":';
        $middle = json_encode($availability);
        $last = '}]';
        $firstavailability = $first.$middle.$last;
        
        $microtime = $availability[0]->date;
        $month = date("n",$microtime/1000);
        $year = date("Y",$microtime/1000);
        $embedded = "false";

        return view('toursdk::booking.calendar')->with(['currency'=>$currency,'lang'=>$lang,'embedded'=>$embedded,'contents'=>$contents,'pickup'=>$pickup,'sessionId'=>$sessionId,'bookingChannelUUID'=>$bookingChannelUUID,'firstavailability'=>$firstavailability,'year'=>$year,'month'=>$month]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(BookingDataTable $dataTable)
    {
        return $dataTable->render('toursdk::booking.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('toursdk::booking.create',['products'=>$products]);
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
    public function update(Request $request, $id)
    {
        if($request->input('update')!="")
        {
            $validator = Validator::make($request->all(), [
                    'update' => 'in:capture,void'
            ]);
                
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json($errors);
            }

            $shoppingcart = Shoppingcart::findOrFail($id);
            $update = $request->input('update');
            if($update=="capture")
            {
                PaypalHelper::captureAuth($shoppingcart->shoppingcart_payment->authorization_id);
                $shoppingcart->shoppingcart_payment->payment_status = 2;
                $shoppingcart->shoppingcart_payment->save();
                $shoppingcart->booking_status = 'CONFIRMED';
                $shoppingcart->save();
            }
            if($update=="void")
            {

                PaypalHelper::voidPaypal($shoppingcart->shoppingcart_payment->authorization_id);
                $shoppingcart->shoppingcart_payment->payment_status = 3;
                $shoppingcart->shoppingcart_payment->save();
                $shoppingcart->booking_status = 'CANCELED';
                $shoppingcart->save();
            }
            return response()->json([
                        "id"=>"1",
                        "message"=>'success'
                    ]);
        }

        if($request->input('action')=="cancel")
        {
            $shoppingcart = Shoppingcart::findOrFail($id);
            $shoppingcart->booking_status = 'CANCELED';
            $shoppingcart->save();
            return response()->json([
                        "id"=>"1",
                        "message"=>'success'
                    ]);
            
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shoppingcart = Shoppingcart::findOrFail($id);
        $shoppingcart->delete();
    }
}
