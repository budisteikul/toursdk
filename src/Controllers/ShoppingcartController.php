<?php

namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Models\Shoppingcart;
use budisteikul\toursdk\Models\ShoppingcartProduct;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\PaypalHelper;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade as PDF;


class ShoppingcartController extends Controller
{

    public function webhook($bokun_accesskey,$bokun_secretkey,Request $request)
    {
        if(env("BOKUN_ACCESSKEY")== $bokun_accesskey && env("BOKUN_SECRETKEY")==$bokun_secretkey)
        {
            $data = $request->all();
            switch($request->input('action'))
            {
            case 'BOOKING_CONFIRMED':
                //print_r($data);
                $shopping_carts = BookingHelper::webhook_insert_shoppingcart($data);
                return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
            break;
            case 'BOOKING_ITEM_CANCELLED':
                $shopping_cart = Shoppingcart::where('confirmation_code',$data['confirmationCode'])->firstOrFail();
                $shopping_cart->booking_status = "CANCELED";
                $shopping_cart->save();
                return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
            break;
            }
        }
        return response()->json([
                    "id" => "2",
                    "message" => 'Error'
                ]);
    }

    public function confirmpaymentpaypal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderID' => ['required', 'string', 'max:255'],
            'authorizationID' => ['required', 'string', 'max:255'],
            'sessionId' => ['required', 'string', 'max:255'],
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $orderID = $request->input('orderID');
        $authorizationID = $request->input('authorizationID');
        $sessionId = $request->input('sessionId');
        
        $shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$sessionId)->firstOrFail();

        $grand_total = $shoppingcart->shoppingcart_payment->amount;
        $payment_total = PaypalHelper::getOrder($orderID);
        
        if($payment_total!=$grand_total)
        {
            PaypalHelper::voidPaypal($authorizationID);
            return response()->json([
                    "id" => "2",
                    "message" => 'Payment Not Valid'
                ]);
        }
        
        $shoppingcart->shoppingcart_payment->order_id = $orderID;
        $shoppingcart->shoppingcart_payment->authorization_id = $authorizationID;
        $shoppingcart->shoppingcart_payment->payment_status = 1;
        $shoppingcart->shoppingcart_payment->save();
        
        BookingHelper::confirm_booking($shoppingcart);

        BookingHelper::shoppingcart_mail($shoppingcart);

        BookingHelper::shoppingcart_clear($shoppingcart);                

        return response()->json([
                    "id" => "1",
                    "message" => $shoppingcart->id .'/'. $shoppingcart->session_id
                ]);
        
    }

    public function confirmpaymentmidtrans(Request $request)
    {
        switch ($request->method()) {
        case 'POST':
            $data = $request->all();
            
            $shoppingcart = Shoppingcart::where('confirmation_code',$data['order_id'])->first();
            if(@count($shoppingcart))
            {
                if($hash = hash('sha512', $shoppingcart->confirmation_code.$data['status_code'].$shoppingcart->shoppingcart_payment->amount.env('MIDTRANS_SERVER_KEY'))==$data['signature_key'])
                {
                    if($data['transaction_status']=="settlement")
                    {
                        $shoppingcart->shoppingcart_payment->status = 2;
                        $shoppingcart->shoppingcart_payment->save();
                        BookingHelper::shoppingcart_mail($shoppingcart);
                        return response('Success', 200)->header('Content-Type', 'text/plain');
                    }
                    else if($data['transaction_status']=="pending")
                    {
                        $shoppingcart->shoppingcart_payment->status = 4;
                        $shoppingcart->shoppingcart_payment->save();
                        return response('Pending', 200)->header('Content-Type', 'text/plain');
                    }
                    else
                    {
                        $shoppingcart->booking_status = 'CANCELED';
                        $shoppingcart->save();
                        return response('Cancel', 200)->header('Content-Type', 'text/plain');
                    }
                
                }
                else
                {
                    return response('Signature failed', 200)->header('Content-Type', 'text/plain');
                }
            }
            else
            {
                return response('Not found', 200)->header('Content-Type', 'text/plain');
            }
            
            
            break;
        default:
            $shoppingcart = Shoppingcart::where('confirmation_code',$request->input('order_id'))->firstOrFail();
            return redirect('/booking/receipt/'.$shoppingcart->id.'/'.$shoppingcart->session_id);
            break;
        }
        
    }

    public function createpaymentpaypal(Request $request)
    {
        $sessionId = $request->header('sessionId');
        $shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$sessionId)->firstOrFail();
       
        $response = BookingHelper::create_payment($shoppingcart,"paypal");
        return response()->json($response);
    }

    public function createpaymentmidtrans(Request $request)
    {
        $sessionId = $request->input('sessionId');
        $shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$sessionId)->firstOrFail();
        
        $response = BookingHelper::create_payment($shoppingcart,"midtrans");
        BookingHelper::confirm_booking($shoppingcart);
        BookingHelper::shoppingcart_clear($shoppingcart);

        return response()->json([
                    "id" => "1",
                    "redirect" => '/booking/receipt/'.$shoppingcart->id.'/'.$shoppingcart->session_id                ]);
    }

    public function invoice($sessionId,$id)
    {
        $shoppingcart = Shoppingcart::where('confirmation_code',$id)->where('session_id',$sessionId)->firstOrFail();
        $notice = '';
        
        if($shoppingcart->currency!=$shoppingcart->shoppingcart_payment->currency)
        {
            $notice .= 'Rate : '. BookingHelper::get_rate($shoppingcart);
        }
        
        $pdf = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.invoice', compact('shoppingcart','notice'))->setPaper('a4', 'portrait');
        return $pdf->download('Invoice-'. $shoppingcart->confirmation_code .'.pdf');
    }
    
    public function ticket($sessionId,$id)
    {
        $shoppingcart_product = ShoppingcartProduct::where('product_confirmation_code',$id)->whereHas('shoppingcart', function($query) use ($sessionId){
            return $query->where('session_id', $sessionId);
        })->firstOrFail();
        $customPaper = array(0,0,300,540);
        $pdf = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.ticket', compact('shoppingcart_product'))->setPaper($customPaper);
        return $pdf->download('Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf');
    }

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
            $shoppingcart = Shoppingcart::where('booking_status','CART')->where('session_id',$sessionId)->firstOrFail();

            

            $skip_payment = $request->input('skip_payment');
            if($skip_payment=="") $skip_payment = false;
            if($skip_payment)
            {
                $bookingChannel = $request->input('bookingChannel');
                $shoppingcart = BookingHelper::save_question($shoppingcart,$request);
                $shoppingcart->booking_channel = $bookingChannel;
                $shoppingcart->booking_status = 'CONFIRMED';
                $shoppingcart->save();
                BookingHelper::shoppingcart_clear($shoppingcart);
            }
            else
            {
                $check_question = BookingHelper::check_question($shoppingcart,$request);
                if(@count($check_question) > 0)
                {
                    return response()->json($check_question);
                }
                $shoppingcart = BookingHelper::save_question($shoppingcart,$request);
                $shoppingcart->save();

            }

            return response()->json([
                    "id" => "1",
                    "message" => ''
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
            'bookingId' => ['required', 'string', 'max:255'],
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
        $shoppingcart = Shoppingcart::where('session_id',$sessionId)->where('booking_status','CART')->count();
        if($shoppingcart > 0)
        {
            BookingHelper::get_shoppingcart($sessionId,"update");
        }
        else
        {
            BookingHelper::get_shoppingcart($sessionId,"insert");
        }
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
