<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Http\Request;


use budisteikul\toursdk\Helpers\BokunHelper;
use budisteikul\toursdk\Helpers\ImageHelper;
use budisteikul\toursdk\Helpers\ProductHelper;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\GeneralHelper;
use budisteikul\toursdk\Models\Category;
use budisteikul\toursdk\Models\Product;
use Html2Text\Html2Text;
use Carbon\Carbon;

class ContentHelper {

	public static function env_paypalCurrency()
    {
        return env("PAYPAL_CURRENCY");
    }

    public static function env_appUrl()
    {
        return env("APP_URL");
    }

    public static function env_appApiUrl()
    {
        return env("APP_API_URL");
    }

    public static function env_appName()
    {
        return env("APP_NAME");
    }

    public static function env_paypalClientId()
    {
        return env("PAYPAL_CLIENT_ID");
    }

    public static function env_midtransClientKey()
    {
        return env("MIDTRANS_CLIENT_KEY");
    }

    public static function env_midtransEnv()
    {
        return env("MIDTRANS_ENV");
    }

    public static function env_appAssetUrl()
    {
        return env("APP_ASSET_URL");
    }

    public static function view_shoppingcart($shoppingcart)
    {
        
        $dataShoppingcart = array();
        $dataProducts = array();

        foreach(collect($shoppingcart->products)->sortBy('booking_id') as $shoppingcart_product)
        {
            
            $product_subtotal = 0;
            $product_discount = 0;
            $product_total = 0;
            $product_detail_asText = '';
            

            foreach($shoppingcart_product->product_details as $product_detail)
            {
                //
                if($product_detail->type=="product")
                {
                    $product_subtotal += $product_detail->subtotal;
                    $product_discount += $product_detail->discount;
                    $product_total += $product_detail->total;
                    $product_detail_asText .= $product_detail->qty .' x '. $product_detail->unit_price .' ('. GeneralHelper::numberFormat($product_detail->price) .') <br />';
                }

            }

            

            if($product_discount>0)
            {
                $product_total_asText = '<strike class="text-muted">'.GeneralHelper::numberFormat($product_subtotal).'</strike><br /><b>'.GeneralHelper::numberFormat($product_total).'</b>';
            }
            else
            {
                $product_total_asText = '<b>'.GeneralHelper::numberFormat($product_total).'</b>';
            }

            $dataPickup = array();
            foreach($shoppingcart_product->product_details as $product_detail)
            {
                if($product_detail->type=="pickup")
                {
                    if($product_detail->discount > 0)
                    {
                        $pickup_price_asText = '<strike class="text-muted">'. GeneralHelper::numberFormat($product_detail->subtotal) .'</strike><br /><b>'. GeneralHelper::numberFormat($product_detail->total) .'</b>';
                    }
                    else
                    {
                        $pickup_price_asText = '<b>'. GeneralHelper::numberFormat($product_detail->total) .'</b>';
                    }

                    $dataPickup[] = array(
                        'title' => 'Pick-up and drop-off services',
                        'price' => $pickup_price_asText,
                        'unit_price' => $product_detail->unit_price,
                    );

                }
            }

            
            $dataExtra = array();
            foreach($shoppingcart_product->product_details as $product_detail)
            {
                if($product_detail->type=="extra")
                {
                    
                    $extra_unit_price_asText = $product_detail->qty .' '. $product_detail->unit_price;
                    if($product_detail->discount > 0)
                    {
                        $extra_price_asText = '<strike class="text-muted">'. GeneralHelper::numberFormat($product_detail->subtotal) .'</strike><br /><b>'. GeneralHelper::numberFormat($product_detail->total) .'</b>';
                    }
                    else
                    {
                        $extra_price_asText = '<b>'. GeneralHelper::numberFormat($product_detail->total) .'</b>';
                    }

                    

                    $dataExtra[] = array(
                        'title' => $extra_unit_price_asText,
                        'price' => $extra_price_asText,
                        'unit_price' => 'Per booking',
                    );

                }
            }



            $dataProducts[] = array(
                'booking_id' => $shoppingcart_product->booking_id,
                'title' => $shoppingcart_product->title,
                'product_total' => $product_total_asText,
                'image' => $shoppingcart_product->image,
                'date' => ProductHelper::datetotext($shoppingcart_product->date),
                'rate' => $shoppingcart_product->rate,
                'product_detail' => $product_detail_asText,
                'pickups' => $dataPickup,
                'extras' => $dataExtra,
            );
            
        }    
        
           
        $dataMainQuestion = array();
        foreach(collect($shoppingcart->questions)->sortBy('order') as $shoppingcart_question)
        {
            if($shoppingcart_question->type=='mainContactDetails')
            {
                $dataMainQuestion[] = array(
                    'question_id' => $shoppingcart_question->question_id,
                    'required' => $shoppingcart_question->required,
                    'data_format' => $shoppingcart_question->data_format,
                    'label' => $shoppingcart_question->label,
                    'answer' => $shoppingcart_question->answer,
                );
            }
        }

        

        $dataProductQuestion = array();
        foreach(collect($shoppingcart->products)->sortBy('booking_id') as $shoppingcart_product)
        {
            
            $dataQuestionBooking = array();
            foreach(collect($shoppingcart->questions)->sortBy('order') as $shoppingcart_question)
            {

                if($shoppingcart_product->booking_id==$shoppingcart_question->booking_id)
                {
                    
                    if($shoppingcart_question->when_to_ask=="booking")
                    {
                        
                        $dataQuestion_options = array();

                        if($shoppingcart_question->data_type=="OPTIONS")
                        {
                            foreach(collect($shoppingcart_question->question_options)->sortBy('order') as $question_option)
                            {
                                $dataQuestion_options[] = array(
                                    'label' => $question_option->label,
                                    'value' => $question_option->value,
                                    'order' => $question_option->order,
                                );
                            }
                        }

                        $dataQuestionBooking[] = array(
                            'question_id' => $shoppingcart_question->question_id,
                            'required' => $shoppingcart_question->required,
                            'when_to_ask' => $shoppingcart_question->when_to_ask,
                            'data_type' => $shoppingcart_question->data_type,
                            'label' => $shoppingcart_question->label,
                            'help' => $shoppingcart_question->help,
                            'answer' => $shoppingcart_question->answer,
                            'booking_id' => $shoppingcart_question->booking_id,
                            'question_options' => $dataQuestion_options,
                        );
                    }

                    


                }

            }

            
            $dataQuestionParticipant = array();
            foreach(collect($shoppingcart->questions)->sortBy('order') as $shoppingcart_question)
            {
                

                if($shoppingcart_product->booking_id===$shoppingcart_question->booking_id)
                {
                    

                    if($shoppingcart_question->when_to_ask=="participant")
                    {
        
                        $dataQuestion_options = array();

                        if($shoppingcart_question->data_type=="OPTIONS")
                        {
                            foreach(collect($shoppingcart_question->question_options)->sortBy('order') as $question_option)
                            {
                                $dataQuestion_options[] = array(
                                    'label' => $question_option->label,
                                    'value' => $question_option->value,
                                    'order' => $question_option->order,
                                );
                            }
                        }

                        $dataQuestionParticipant[] = array(
                            'question_id' => $shoppingcart_question->question_id,
                            'required' => $shoppingcart_question->required,
                            'when_to_ask' => $shoppingcart_question->when_to_ask,
                            'participant_number' => $shoppingcart_question->participant_number,
                            'data_type' => $shoppingcart_question->data_type,
                            'label' => $shoppingcart_question->label,
                            'help' => $shoppingcart_question->help,
                            'answer' => $shoppingcart_question->answer,
                            'booking_id' => $shoppingcart_question->booking_id,
                            'question_options' => $dataQuestion_options,
                        );
                    }
                }
                
            }
            
            $collection = collect($dataQuestionParticipant)->sortBy('participant_number');
            $grouped = $collection->groupBy(function ($item, $key) {
                    return 'Traveller '.$item['participant_number'];
                });
            

            $dataProductQuestion[] = array(
                        'title' => $shoppingcart_product->title,
                        'description' => ProductHelper::datetotext($shoppingcart_product->date),
                        'questions' => $dataQuestionBooking,
                        'question_participants' => $grouped,
                    );
            
        }

        $promo_code = $shoppingcart->promo_code;
        if($promo_code=="") $promo_code = null;
        
        $payment_enable = 'qris,xendit,paypal';
        $payment_default = 'qris';

        //================================================
        
        

        if(env('PAYPAL_INTENT')=="CAPTURE")
        {
            //$paypal_sdk = 'https://www.paypal.com/sdk/js?client-id='.self::env_paypalClientId().'&currency='. self::env_paypalCurrency().'&disable-funding=card';
            $paypal_sdk = 'https://www.paypal.com/sdk/js?client-id='.self::env_paypalClientId().'&currency='. self::env_paypalCurrency().'';
        }
        else
        {
            //$paypal_sdk = 'https://www.paypal.com/sdk/js?client-id='.self::env_paypalClientId().'&intent=authorize&currency='. self::env_paypalCurrency().'&disable-funding=card';
            $paypal_sdk = 'https://www.paypal.com/sdk/js?client-id='.self::env_paypalClientId().'&intent=authorize&currency='. self::env_paypalCurrency().'';
        }
        
        $rate_text = '';
        if($shoppingcart->currency!="USD") $rate_text = 'Charge in USD, '. BookingHelper::text_rate($shoppingcart,self::env_paypalCurrency());

        $dataShoppingcart[] = array(

                'id' => $shoppingcart->session_id,
                'confirmation_code' => $shoppingcart->confirmation_code,
                'promo_code' => $shoppingcart->promo_code,
                'currency' => $shoppingcart->currency,
                'subtotal' => GeneralHelper::numberFormat($shoppingcart->subtotal),
                'discount' => GeneralHelper::numberFormat($shoppingcart->discount),
                'total' => GeneralHelper::numberFormat($shoppingcart->total),
                'due_now' => GeneralHelper::numberFormat($shoppingcart->due_now),
                'due_on_arrival' => GeneralHelper::numberFormat($shoppingcart->due_on_arrival),
                'products' => $dataProducts,
                'mainQuestions' => $dataMainQuestion,
                'productQuestions' => $dataProductQuestion,
                
                'payment_enable' => $payment_enable,
                'payment_default' => $payment_default,

                //Card
                'xendit_currency' => 'IDR',
                'xendit_total' =>  GeneralHelper::numberFormat(BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,'IDR'),'IDR'),
                'xendit_rate' => '
                    <img src="'. self::env_appAssetUrl() .'/img/payment/payment-channel-xendit.png" style="max-height:40px" class="img-fluid" alt="Payment Logo" />',
                'xendit_label' => '<strong class="mb-1">Card Payments</strong>',

                //QRIS
                'qris_currency' => 'IDR',
                'qris_total' =>  GeneralHelper::numberFormat(BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,'IDR'),'IDR'),
                'qris_rate' => '<img src="'. self::env_appAssetUrl() .'/img/payment/qris.png" style="max-height:40px" class="img-fluid" alt="Payment Logo" />',
                'qris_label' => '<strong class="mb-1">Scan to Pay</strong>',

                // Paypal Currency
                'paypal_currency' => self::env_paypalCurrency(),
                'paypal_total' => GeneralHelper::numberFormat(BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,self::env_paypalCurrency()),'USD'),
                'paypal_rate' => $rate_text,
                'paypal_sdk' => $paypal_sdk,
                'paypal_label' => '<strong class="mb-1"><img src="'. self::env_appAssetUrl() .'/img/payment/paypal.png" height="25" alt="Paypal" /></strong>',

                // Stripe Currency
                'stripe_currency' => 'USD',
                'stripe_total' => GeneralHelper::numberFormat(BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,'USD'),'USD'),
                'stripe_rate' => $rate_text,
                'stripe_label' => '
                <strong class="mb-1">Alt Card Payments 
                    <img class="ml-2" src="'. self::env_appAssetUrl() .'/img/payment/stripe.png" height="20" alt="Card Payment" />
                </strong>
                <div class="ml-0 mb-1 mt-2">
                    <img src="'. self::env_appAssetUrl() .'/img/payment/card-payment.png" style="max-height:40px" class="img-fluid" alt="Payment Logo" />
                </div>',

                
            );

        return $dataShoppingcart;
    }

	public static function view_receipt($shoppingcart)
	{
		$invoice = 'Invoiced to '.$shoppingcart->booking_channel;
        try {
            if($shoppingcart->booking_channel=="WEBSITE") {
                if($shoppingcart->shoppingcart_payment->payment_status>0) {
                    $invoice = '<a target="_blank" class="text-theme" href="'.url('/api').'/pdf/invoice/'. $shoppingcart->session_id .'/Invoice-'. $shoppingcart->confirmation_code .'.pdf"><i class="fas fa-file-invoice"></i> Invoice-'. $shoppingcart->confirmation_code .'.pdf</a><br />';
                }
            }
        } catch (Exception $e) {

        }

        $ticket = '';
        try {
            if($shoppingcart->shoppingcart_payment->payment_status==2 || $shoppingcart->shoppingcart_payment->payment_status==1) {
                foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product) {
                    $ticket .= '<a target="_blank" class="text-theme" href="'.url('/api').'/pdf/ticket/'.$shoppingcart->session_id.'/Ticket-'.$shoppingcart_product->product_confirmation_code.'.pdf"><i class="fas fa-ticket-alt"></i> Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf</a>
                                <br />';
                }
            }
        } catch (Exception $e) {
        }
        
        if($ticket=="") $ticket = 'No Documents <br /><small class="form-text text-muted">* Available when status is paid</small>';
        
        $how_to_pay = array();
        
        if($shoppingcart->shoppingcart_payment->payment_type=="qrcode")
        {
            if($shoppingcart->shoppingcart_payment->payment_status==4)
            {
                if($shoppingcart->shoppingcart_payment->bank_name=="paynow")
                {
                    /*
                    $how_to_pay = '
                    <div class="pl-2">
                    1.  Open your <b>Wise</b> app. <br />
                    2.  In your Card or Account tab, swipe the Card Carousell to the left to bring up Pay Like a Local
                    <br />
                    3.  Tap <b>Scan QR</b>. <br />
                    4.  <b>Scan</b> the QR code shown on your monitor. <br />
                    <img width="230" class="mt-2 mb-2" src="'. self::env_appAssetUrl() .'/img/payment/qr-instruction.png">
                    <br />
                    5.  Check your payment details in the <b>Wise</b> app, then tap <b>Pay</b>. <br />
                    6.  Follow the steps to confirm your payment. <br />
                    7.  Done. <br /><br />
                    Alternatively, you can <b>download</b> or <b>screenshot QR code</b> from this site and <b>import</b> it to your <b>Wise</b> app.
                    </div><br />';
                    */
                    $how_to_pay = '
                    <div class="pl-2">
                    1.  Open your <b>E-wallet</b> or <b>Mobile Banking</b> apps. <br />
                    2.  <b>Scan</b> the QR code shown on your monitor. <br />
                    <img width="230" class="mt-2 mb-2" src="'. self::env_appAssetUrl() .'/img/payment/qr-instruction.png">
                    <br />
                    3.  Check your payment details in the app, then tap <b>Pay</b>. <br />
                    4.  Enter your <b>PIN</b>. <br />
                    5.  Your transaction is complete. <br /><br />
                    Alternatively, you can download or screenshot QR code from this site and import it to your E-wallet or Mobile Banking apps.
                    </div><br />';
                }
                else
                {
                    $how_to_pay = '
                    <div class="pl-2">
                    1.  Open your <b>E-wallet</b> or <b>Mobile Banking</b> apps. <br />
                    2.  <b>Scan</b> the QR code shown on your monitor. <br />
                    <img width="230" class="mt-2 mb-2" src="'. self::env_appAssetUrl() .'/img/payment/qr-instruction.png">
                    <br />
                    3.  Check your payment details in the app, then tap <b>Pay</b>. <br />
                    4.  Enter your <b>PIN</b>. <br />
                    5.  Your transaction is complete. <br /><br />
                    Alternatively, you can download or screenshot QR code from this site and import it to your E-wallet or Mobile Banking apps.
                    </div><br />';
                }
                
            }
        }

        
        if($shoppingcart->shoppingcart_payment->payment_type=="bank_transfer")
        {
            if($shoppingcart->shoppingcart_payment->payment_status==4)
            {   
                if($shoppingcart->shoppingcart_payment->currency=="SGD")
                {
                    $how_to_pay = 'Please Transfer funds to the provided DBS BANK account using your Singapore based bank account via FAST Transfer';
                }
                else
                {
                    //$how_to_pay = 'Log in to bank and transfer funds to the provided '. strtoupper($shoppingcart->shoppingcart_payment->bank_name) .' account to complete the transaction. BI-FAST transfer not recommended, it may will cause payment failed';
                    $how_to_pay = 'Log in to bank and transfer funds to the provided '. strtoupper($shoppingcart->shoppingcart_payment->bank_name) .' account to complete the transaction.';
                }
                
                
            } 


        }
        
        if($shoppingcart->shoppingcart_payment->payment_type=="cash")
        {
            if(strtolower($shoppingcart->shoppingcart_payment->bank_name)=="alfamart")
            {
                if($shoppingcart->shoppingcart_payment->payment_status==4)
                {
                $how_to_pay = '
                    <div class="pl-2">
                    1. Take a screenshot this screen and go to your nearest <b>Alfamart</b> store. <br />
                    2. Tell the cashier that you wish to make a <b>'. strtoupper($shoppingcart->shoppingcart_payment->payment_provider) .' VIA DOKU</b> payment. <br />
                    3. Show the screenshot to the cashier.<br />
                    <small>Additional small fee may be applicable per transaction.</small>
                    </div><br />';
                }
            }
        }

        if($shoppingcart->shoppingcart_payment->payment_type=="other")
        {
            if(strtolower($shoppingcart->shoppingcart_payment->bank_name)=="npp")
            {
                $how_to_pay = '
                    <div class="pl-2">
                    1. Copy the PayID mentioned in this page. <br />
                    2. Open your mobile banking App and select NPP funds transfer. <br />
                    3. Transfer the funds to the PayID copied in step 1.<br />
                    </div><br />';
            }
        }

        $payment_status_asText = BookingHelper::get_paymentStatus($shoppingcart);
        $booking_status_asText = BookingHelper::get_bookingStatus($shoppingcart);
        
        $main_contact = BookingHelper::get_answer_contact($shoppingcart);
        $due_date = Carbon::createFromFormat('Y-m-d H:i:s', BookingHelper::due_date($shoppingcart,"database"));
        
        $dataObj = array(
            'vendor' => self::env_appName(),
            'booking_status' => $shoppingcart->booking_status,
            'booking_status_asText' => $booking_status_asText,
            'confirmation_code' => $shoppingcart->confirmation_code,
            'total' => $shoppingcart->currency .' '. GeneralHelper::numberFormat($shoppingcart->due_now),
            'payment_status' => $shoppingcart->shoppingcart_payment->payment_status,
            'payment_status_asText' => $payment_status_asText,
            'firstName' => $main_contact->firstName,
            'lastName' => $main_contact->lastName,
            'phoneNumber' => $main_contact->phoneNumber,
            'email' => $main_contact->email,
            'invoice' => $invoice,
            'tickets' => $ticket,
            'paymentProvider' => $shoppingcart->shoppingcart_payment->payment_provider,
            'pdf_url' => $how_to_pay,
            'how_to_pay' => $how_to_pay,
            'due_date' => $due_date
        );

        return $dataObj;
	}


	

	public static function view_last_order($shoppingcarts)
	{
		$booking = array();


        foreach($shoppingcarts as $shoppingcart)
        {
            $invoice = self::view_invoice($shoppingcart);

            $product = self::view_product_detail($shoppingcart);
            
            $receipt_page = '<a onclick="window.openAppRoute(\'/booking/receipt/'.$shoppingcart->session_id.'/'. $shoppingcart->confirmation_code .'\')"  class="btn btn-theme" href="javascript:void(0);">View receipt page <i class="fas fa-arrow-circle-right"></i></a>';

            $booking[] = array(
                'booking' => $product . $receipt_page
            );
        }
        return $booking;
	}

	public static function view_categories()
	{
		$dataObj = array();
        $categories = Category::get();
        foreach($categories as $category)
        {
            $dataObj2 = array();
            foreach($category->product()->orderBy('updated_at','desc')->get() as $product)
            {
                
                $content = BokunHelper::get_product($product->bokun_id);
                $cover = ImageHelper::cover($product);
                $dataObj2[] = array(
                    'id' => $product->id,
                    'cover' => $cover,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'excerpt' => $content->excerpt,
                    'duration' => $content->durationText,
                    'currency' => $content->nextDefaultPriceMoney->currency,
                    'amount' => GeneralHelper::numberFormat($content->nextDefaultPriceMoney->amount),
                );
                
            }

            $dataObj[] = array(
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'products' => $dataObj2,
            );


        }
        return $dataObj;
	}

	public static function view_category($category)
	{
		$products = ProductHelper::getProductByCategory($category->id);

		$dataObj = array();
        $dataObj2 = array();
        foreach($products as $product)
        {
            
            $content = BokunHelper::get_product($product->bokun_id);
            
            $cover = ImageHelper::cover($product);
            $dataObj2[] = array(
                'id' => $product->id,
                'cover' => $cover,
                'name' => $product->name,
                'slug' => $product->slug,
                'excerpt' => $content->excerpt,
                'duration' => $content->durationText,
                'currency' => $content->nextDefaultPriceMoney->currency,
                'amount' => GeneralHelper::numberFormat($content->nextDefaultPriceMoney->amount),
            );
            
        }
        
        $dataObj[] = array(
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'products' => $dataObj2,
            );

        return $dataObj;
	}

	public static function view_product($product)
	{
		$dataObj = array();
        $dataObj2 = array();
        $dataObj3 = array();
        $dataObj4 = array();
        $dataObj5 = array();
        
        $content = BokunHelper::get_product($product->bokun_id);

        
        $i = 0;
        $carouselExampleIndicators = '';
        $carouselInners = '';
        foreach($product->images->sortBy('sort') as $image)
        {
            $active = '';
            if($i==0) $active = 'active';

            $carouselInners .= '<div class="carousel-item '.$active.'"><img class="d-block w-100" src="'.ImageHelper::urlImageGoogle($image->public_id,600,400).'" alt="'.$product->name.'"  /></div>';

            $carouselExampleIndicators .= '<li data-target="#carouselExampleIndicators" data-slide-to="'.$i.'"></li>';

            $i++;
        }

        $image = '
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                '.$carouselExampleIndicators.'
            </ol>

            <div class="carousel-inner">
                '.$carouselInners.'
            </div>
          
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>';

        $pickup = '';
        if($content->meetingType=='PICK_UP' || $content->meetingType=='MEET_ON_LOCATION_OR_PICK_UP')
        {
            $pickup = BokunHelper::get_product_pickup($content->id);
        }


        if(!empty($pickup))
        {
            for($i=0;$i<count($pickup);$i++)
            {
                $dataObj3[] = array(
                            'title' => $pickup[$i]->title,
                        );
            }
        }


        if(!empty($content->startPoints))
        {
            $dataObj2[] = array(
                            'title' => $content->startPoints[0]->title,
                            'addressLine1' => $content->startPoints[0]->address->addressLine1,
                            'addressLine2' => $content->startPoints[0]->address->addressLine2,
                            'addressLine3' => $content->startPoints[0]->address->addressLine3,
                            'city' => $content->startPoints[0]->address->city,
                            'state' => $content->startPoints[0]->address->state,
                            'postalCode' => $content->startPoints[0]->address->postalCode,
                            'countryCode' => $content->startPoints[0]->address->countryCode,
                            'latitude' => $content->startPoints[0]->address->geoPoint->latitude,
                            'longitude' => $content->startPoints[0]->address->geoPoint->longitude
                        );
        }

        $difficultyLevel = '';
        if($content->difficultyLevel!="") $difficultyLevel = ProductHelper::lang('dificulty',$content->difficultyLevel);

        $productCategory = ProductHelper::lang('type',$content->productCategory);

        if(!empty($content->guidanceTypes))
        {
            if($content->guidanceTypes[0]->guidanceType=="GUIDED")
            {
                for($i=0;$i<count($content->guidanceTypes[0]->languages);$i++)
                {
                    $dataObj4[] = array(
                            'language' => ProductHelper::lang('language',$content->guidanceTypes[0]->languages[$i]),
                        );
                    
                }
            }
        }


        if(!empty($content->agendaItems))
        {
            foreach($content->agendaItems as $agendaItem)
            {
                $dataObj5[] = array(
                    'title' => $agendaItem->title,
                    'body' => $agendaItem->body,
                );
            }
        }

        $excerpt = null;
        $included = null;
        $excluded = null;
        $requirements = null;
        $attention = null;
        $durationText = null;
        $privateActivity = null;
        $description = null;
        

        if($content->excerpt!="") $excerpt = $content->excerpt;
        if($content->included!="") $included = $content->included;
        if($content->excluded!="") $excluded = $content->excluded;
        if($content->requirements!="") $requirements = $content->requirements;
        if($content->attention!="") $attention = $content->attention;
        if($content->durationText!="") $durationText = $content->durationText;
        if($content->privateActivity!="") $privateActivity = $content->privateActivity;
        if($content->description!="") $description = $content->description;

        $data_voucher = null;
        $voucher = $product->vouchers->first();
        if(isset($voucher))
        {
            if($voucher->is_percentage)
            {
                $data_voucher = '<div class="alert alert-warning" role="alert"><i class="fas fa-tags"></i> Get a <strong>Discount '. $voucher->amount .'%</strong> with promotional code <strong>'. $voucher->code .'</strong> on this activity</div>';
            }
            else
            {
                $data_voucher = '<div class="alert alert-warning" role="alert"><i class="fas fa-tags"></i> Get a <strong>Discount IDR '. GeneralHelper::numberFormat($voucher->amount,"IDR") .'</strong> with promotional code <strong>'. $voucher->code  .'</strong> on this activity</div>';
            }
        }
       

        $dataObj[] = array(
                'id' => $product->id,
                'name' => $product->name,
                'durationText' => $durationText,
                'difficultyLevel' => $difficultyLevel,
                'privateActivity' => $privateActivity,
                'excerpt' => $excerpt,
                'startPoints' => $dataObj2,
                'description' => $description,
                'included' => $included,
                'excluded' => $excluded,
                'requirements' => $requirements,
                'attention' => $attention,
                'pickupPlaces' => $dataObj3,
                'productCategory' => $productCategory,
                'guidanceTypes' => $dataObj4,
                'agendaItems' => $dataObj5,
                'promotion' => $data_voucher,
                'images' => $image,
            );

        return $dataObj;
	}

	

    public static function view_invoice($shoppingcart)
    {
        $invoice = '<div class="card mb-2"><div class="card-body bg-light">';
        $invoice1 = '<b><a class="text-decoration-none text-theme" href="'.url('/api').'/pdf/invoice/'. $shoppingcart->session_id .'/Invoice-'. $shoppingcart->confirmation_code .'.pdf" target="_blank">'. $shoppingcart->confirmation_code .'</a> - INVOICE</b>';

        $invoice .= $invoice1.'</br>';
        $invoice .= 'Channel : '.$shoppingcart->booking_channel.'</br>';

        $main_contact = BookingHelper::get_answer_contact($shoppingcart);

        $first_name = $main_contact->firstName;
        $last_name = $main_contact->lastName;
        $email = $main_contact->email;
        $phone = $main_contact->phoneNumber;

        if($first_name!='' || $last_name!='') $invoice .= 'Name : '.$first_name.' '. $last_name .' 
        <input type="hidden" id="full_name" value="'.$first_name.' '. $last_name .'"> <button onclick="copyToClipboard(\'#full_name\')" title="Copied" data-toggle="tooltip" data-placement="right" data-trigger="click" class="btn btn-light btn-sm invoice-hilang"><i class="far fa-copy"></i></button></br>';
        if($email!='') $invoice .= 'Email : '.$email .'</br>';
        if($phone!='') $invoice .= 'Phone : '.$phone .'</br>';

        $invoice .= 'Status : '. BookingHelper::get_bookingStatus($shoppingcart) .'</br>';
        $invoice .= '</div></div>';

        return $invoice;
    }

	public static function view_product_detail($shoppingcart,$html2text=false)
	{
		$product = '';
		//$access_ticket = BookingHelper::access_ticket($shoppingcart);

		foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
		{
			$product .= '<div class="card mb-2"><div class="card-body bg-light">';

			$product .= '<strong>'.$shoppingcart_product->title.'</strong><br />';
			
            $thedate = ProductHelper::datetotext($shoppingcart_product->date);
            if($thedate!=null) $product .= $thedate .' <br />';
            
			if($shoppingcart_product->rate!="") $product .= $shoppingcart_product->rate .' <br />';
			

			foreach($shoppingcart_product->shoppingcart_product_details()->get() as $shoppingcart_product_detail)
			{
				if($shoppingcart_product_detail->type=="product" || $shoppingcart_product_detail->type=="extra")
				{
					if($shoppingcart_product_detail->unit_price == "Price per booking")
					{
						$product .= $shoppingcart_product_detail->qty .' '. $shoppingcart_product_detail->unit_price .' ('. $shoppingcart_product_detail->people .' Person)<br>';
					}
					else
					{
						$product .= $shoppingcart_product_detail->qty .' '. $shoppingcart_product_detail->unit_price .'<br>';
					}
                                
				}
				elseif($shoppingcart_product_detail->type=="pickup")
				{
					$product .= $shoppingcart_product_detail->title .'<br>';
				}
			}

			
			$product .= '<br />'. BookingHelper::get_answer_product($shoppingcart,$shoppingcart_product->booking_id) .'<br />';
			

			
			$product .= '</div></div>';
		}

		if($html2text)
        {
            $text = New Html2Text($product);
            $product = $text->getText();
        }

		return $product;
	}

}
