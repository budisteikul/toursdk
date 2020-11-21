<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\PaypalHelper;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Validator;

class ShoppingcartController extends Controller
{
    public function checkout(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'sessionId' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json($errors);
            }
        
            $sessionId = $request->input('sessionId');
            $bookingChannel = $request->input('bookingChannel');
            $shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$sessionId)->firstOrFail();

            $shoppingcart = BookingHelper::save_question($shoppingcart,$request);

            $shoppingcart->booking_channel = $bookingChannel;
            $shoppingcart->booking_status = 'CONFIRMED';
            $shoppingcart->save();

            BookingHelper::clear_cart($shoppingcart);

            return response()->json([
                    "id" => "1",
                    "message" => $shoppingcart->id
                ]);
    }

    public function removepromocode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        
        $sessionId = $request->input('sessionId');
        $shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$sessionId)->firstOrFail();
        
        $shoppingcart = BookingHelper::remove_promocode($shoppingcart);
        
            return response()->json([
                    "id" => "1",
                    "message" => $shoppingcart->session_id
                ]);
    }

    public function applypromocode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promocode' => ['required', 'string', 'max:255'],
            'sessionId' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        
        $promocode = $request->input('promocode');
        $sessionId = $request->input('sessionId');

        $shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$sessionId)->firstOrFail();
        $status = BookingHelper::apply_promocode($shoppingcart,$promocode);

        if($status)
        {
            return response()->json([
                    "id" => "1",
                    "message" => 'success'
                ]);
        }
        else
        {
            return response()->json([
                    "id" => "2",
                    "message" => 'failed'
                ]);
        }
    }

    public function removebookingid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'promocode' => ['required', 'string', 'max:255'],
            'sessionId' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $sessionId = $request->input('sessionId');
        $bookingId = $request->input('bookingId');
        
        $shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$sessionId)->firstOrFail();
        $shoppingcart = BookingHelper::remove_activity($shoppingcart,$bookingId);
        
            return response()->json([
                    "id" => "1",
                    "message" => $shoppingcart->sessionId
                ]);
    }

    public function shoppingcart(Request $request)
    {
        $sessionId = $request->input('sessionId');
        BookingHelper::get_shoppingcart($sessionId,"insert");
        return response()->json([
                    "id" => "1",
                    "message" => 'success'
                ]);
    }

    public function addshoppingcart($id,Request $request)
    {
        $contents = BokunHelper::get_addshoppingcart($id,$request->all());
        return response()->json($contents);
    }
    
    public function snippetsinvoice(Request $request)
    {
        $contents = BokunHelper::get_invoice($request->all());
        return response()->json($contents);
    }
    
    public function snippetscalendar($activityId,$year,$month)
    {
        $contents = BokunHelper::get_calendar($activityId,$year,$month);
        return response()->json($contents);
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
     * @param  \App\Models\Shoppingcart  $shoppingcart
     * @return \Illuminate\Http\Response
     */
    public function show(Shoppingcart $shoppingcart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shoppingcart  $shoppingcart
     * @return \Illuminate\Http\Response
     */
    public function edit(Shoppingcart $shoppingcart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shoppingcart  $shoppingcart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shoppingcart $shoppingcart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shoppingcart  $shoppingcart
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shoppingcart $shoppingcart)
    {
        //
    }
}
