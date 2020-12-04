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

    public function test()
    {
        /*
        $reviews = DB::table('rev_reviews')->orderBy('date')->get();
        foreach($reviews as $review)
        {
            
            $new_review = new Review();
            $new_review->product_id = 1;
            $new_review->channel_id = 1;

            $new_review->user = $review->user;
            $new_review->title = $review->title;
            $new_review->text = $review->text;
            $new_review->rating = $review->rating;
            $new_review->date = $review->date;
            $new_review->link = $review->link;

            $new_review->save();
            
        }
        
        $sps = DB::table('rev_shoppingcarts')->get();
        foreach($sps as $sp)
        {
            $new_sp = new Shoppingcart();
            $new_sp->booking_status = $sp->bookingStatus;
            $new_sp->session_id = $sp->sessionId;
            $new_sp->booking_channel = $sp->bookingChannel;
            $new_sp->confirmation_code = $sp->confirmationCode;
            $new_sp->promo_code = $sp->promoCode;
            $new_sp->currency = $sp->currency;
            $new_sp->subtotal = $sp->subtotal;
            $new_sp->discount = $sp->discount;
            $new_sp->tax = $sp->tax;
            $new_sp->fee = $sp->fee;
            $new_sp->total = $sp->total;
            $new_sp->save();

            $new_sppay = new ShoppingcartPayment();
            $new_sppay->shoppingcart_id = $new_sp->id;
            $new_sppay->amount = $sp->total;
            $new_sppay->currency = $sp->currency;
            $new_sppay->payment_status = 0;
            $new_sppay->save();

            $spqs = DB::table('rev_shoppingcart_questions')->where('shoppingcarts_id',$sp->id)->get();
            foreach($spqs as $spq)
            {
                $new_spq = new ShoppingcartQuestion();
                $new_spq->shoppingcart_id = $new_sp->id;
                $new_spq->type = $spq->type;
                $new_spq->booking_id = $spq->bookingId;
                $new_spq->question_id = $spq->questionId;
                $new_spq->label = $spq->label;
                $new_spq->data_type = $spq->dataType;
                $new_spq->data_format = $spq->dataFormat;
                $new_spq->required = $spq->required;
                $new_spq->select_option = $spq->selectOption;
                $new_spq->select_multiple = $spq->selectMultiple;
                $new_spq->help = $spq->help;
                $new_spq->order = $spq->order;
                $new_spq->answer = $spq->answer;
                $new_spq->save();
            }

            $spps = DB::table('rev_shoppingcart_products')->where('shoppingcarts_id',$sp->id)->get();
            foreach($spps as $spp)
            {
                $new_spp = new ShoppingcartProduct();
                $new_spp->shoppingcart_id = $new_sp->id;
                $new_spp->booking_id = $spp->bookingId;
                $new_spp->product_confirmation_code = $spp->productConfirmationCode;
                $new_spp->product_id = $spp->productId;
                $new_spp->image = $spp->image;
                $new_spp->title = $spp->title;
                $new_spp->rate = $spp->rate;
                $new_spp->date = $spp->date;
                $new_spp->currency = $spp->currency;
                $new_spp->subtotal = $spp->subtotal;
                $new_spp->discount = $spp->discount;
                $new_spp->tax = $spp->tax;
                $new_spp->fee = $spp->fee;
                $new_spp->total = $spp->total;
                $new_spp->save();

                $sprs = DB::table('rev_shoppingcart_rates')->where('shoppingcart_products_id',$spp->id)->get();

                foreach($sprs as $spr)
                {
                    $new_spr = new ShoppingcartRate();
                    $new_spr->shoppingcart_product_id = $new_spp->id;
                    $new_spr->type = $spr->type;
                    $new_spr->title = $spr->title;
                    $new_spr->qty = $spr->qty;
                    $new_spr->price = $spr->price;
                    $new_spr->unit_price = $spr->unitPrice;
                    $new_spr->currency = $spr->currency;
                    $new_spr->subtotal = $spr->subtotal;
                    $new_spr->discount = $spr->discount;
                    $new_spr->tax = $spr->tax;
                    $new_spr->fee = $spr->fee;
                    $new_spr->total = $spr->total;
                    $new_spr->save();
                }
            }


        }
        */
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
