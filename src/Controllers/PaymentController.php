<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\PaymentHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use budisteikul\toursdk\Helpers\FirebaseHelper;


class PaymentController extends Controller
{
    
	
    public function __construct()
    {
        
    }
    
    public function checkout(Request $request)
    {
            $validator = Validator::make(json_decode($request->getContent(), true), [
                'sessionId' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json($errors);
            }
            
            $data = json_decode($request->getContent(), true);

            $sessionId = $data['sessionId'];
            
            $check_question = BookingHelper::check_question_json($sessionId,$data);
            if(count($check_question) > 0)
            {
                $check_question['message'] = "Oops there was a problem, please check your input and try again.";
                return response()->json($check_question);
            }
            $shoppingcart = BookingHelper::save_question_json($sessionId,$data);
            
            FirebaseHelper::shoppingcart($sessionId);
            
            $payment = $data['payment'];

            switch($payment)
            {
                case 'paypal':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'paypal',
                        'id' => 3
                    ], 200);
                break;

                case 'stripe':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'stripe',
                        'id' => 3
                    ]);
                break;

                case 'xendit':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'xendit',
                        'id' => 3
                    ]);
                break;

                case 'ovo':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'ovo',
                        'id' => 3
                    ]);
                break;

                case 'qris':
                    //VoucherHelper::apply_voucher($sessionId,'LOCALPAYMENT');
                    BookingHelper::set_bookingStatus($sessionId,'PENDING');
                    BookingHelper::set_confirmationCode($sessionId);
                    $response = PaymentHelper::create_payment($sessionId,"xendit","qris");
                break;

                case 'dana':
                    BookingHelper::set_bookingStatus($sessionId,'PENDING');
                    BookingHelper::set_confirmationCode($sessionId);
                    $response = PaymentHelper::create_payment($sessionId,"xendit","dana");
                break;

                case 'bss':
                    BookingHelper::set_bookingStatus($sessionId,'PENDING');
                    BookingHelper::set_confirmationCode($sessionId);
                    $response = PaymentHelper::create_payment($sessionId,"xendit","bss");
                break;

                default:
                    BookingHelper::set_bookingStatus($sessionId,'PENDING');
                    BookingHelper::set_confirmationCode($sessionId);
                    $response = PaymentHelper::create_payment($sessionId,"xendit","invoice");

            }

            if($response->status->id=="0")
            {
                return response()->json([
                    'id' => "0",
                    'message' => $response->status->message,
                ]);
            }

            $shoppingcart = BookingHelper::confirm_booking($sessionId);
            
            $text = null;
            $session_id = $shoppingcart->session_id;
            $confirmation_code = $shoppingcart->confirmation_code;
            $redirect_type = 1;
            $redirect = $shoppingcart->shoppingcart_payment->redirect;

            if($shoppingcart->shoppingcart_payment->payment_type=="ewallet")
            {
                $redirect_type = 2;
                $text = strtoupper($shoppingcart->shoppingcart_payment->bank_name);
            }

            if($shoppingcart->shoppingcart_payment->payment_type=="bank_redirect")
            {
                $redirect_type = 4;
            }

            return response()->json([
                "message" => "success",
                "id" => $redirect_type,
                "redirect" => $redirect,
                "text" => $text,
                "session_id" => $session_id,
                "confirmation_code" => $confirmation_code
            ]);
            
    }
    
    public function createpaymentpaypal(Request $request)
    {
            $sessionId = $request->header('sessionId');
            BookingHelper::set_confirmationCode($sessionId);
            $response = PaymentHelper::create_payment($sessionId,"paypal");
            return response()->json($response->data);
    }

    public function createpaymentstripe(Request $request)
    {
            $sessionId = $request->header('sessionId');
            BookingHelper::set_confirmationCode($sessionId);
            $response = PaymentHelper::create_payment($sessionId,"stripe");
            return response()->json($response->data);
    }

    public function createpaymentxendit(Request $request)
    {
            $sessionId = $request->header('sessionId');
            $tokenId = $request->header('tokenId');
            BookingHelper::set_confirmationCode($sessionId);
            $response = PaymentHelper::create_payment($sessionId,"xendit","card",$tokenId);
            
            if($response->status->id==1)
            {
                BookingHelper::set_bookingStatus($sessionId,'CONFIRMED');
                $shoppingcart = BookingHelper::confirm_booking($sessionId);
                return response()->json([
                    "id" => "1",
                    "message" => "/booking/receipt/".$shoppingcart->session_id."/".$shoppingcart->confirmation_code
                ]);
            }
            else
            {
                return response()->json([
                    "id" => "0",
                    "message" => $response->status->message
                ]);
            }
            
    }

    

    public function createpaymentovo(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'sessionId' => ['required', 'string', 'max:255'],
                'phoneNumber' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'required',
                ]);
            }

            $phoneNumber = $request->input('phoneNumber');
            $sessionId = $request->input('sessionId');

            $shoppingcart = Cache::get('_'. $sessionId);
            
            if(substr($phoneNumber,0,1)=="0")
            {
                $phoneNumber = substr($phoneNumber,1);
            }
            $phoneNumber = "+62". $phoneNumber;

            $xendit = new XenditHelper();
            $response = $xendit->createEWalletOvoCharge($shoppingcart->due_now,$phoneNumber);
            if(isset($response->error_code))
            {
                return response()->json([
                    "message" => "error"
                ]);
            }
            FirebaseHelper::upload_payment('PENDING',$response->reference_id,$sessionId,'ovo');
            return response()->json([
                    "message" => "success",
                    "reference_id" => $response->reference_id
                ]);
            
    }


    public function ovo_jscript($sessionId)
    {
        $shoppingcart = Cache::get('_'. $sessionId);
        $jscript = '
            $("#submitCheckout").slideUp("slow");
            $("#paymentContainer").html(\'<div class="form mb-2 mt-2"><strong>Please input your OVO number :</strong></div><div class="form-row mb-4 mt-2"><div class="col-xs-2"><input type="text" style="height:47px; width:50px;" class="form-control  disabled" value="+62" disabled></div><div class="col"><input id="ovoPhoneNumber" type="text" style="height:47px;" class="form-control" placeholder="85743112112"></div></div><div id=\"text-alert\" class=\"text-center mb-4 mt-2\"></div><button id="submit" onClick="createpaymentovo()" class="btn btn-lg btn-block btn-theme" style="height:47px"><strong>Click to pay with <img class="ml-2 mr-2" src="'.config('site.assets').'/img/payment/ovo-light.png" height="30" /></strong></button>\');

            function createpaymentovo()
            {
                            var phoneNumber = document.getElementById("ovoPhoneNumber").value;
                            
                            $("#ovoPhoneNumber").removeClass("is-invalid");
                            $("#alert-payment").slideUp("slow");
                            $("#ovoPhoneNumber").attr("disabled", true);
                            $("#submit").attr("disabled", true);
                            $("#submit").html(\' <i class="fa fa-spinner fa-spin fa-fw"></i>  processing... \');

                            $("#text-alert").show();
                            $("#text-alert").html( "Please check OVO app on your mobile phone, to process payment" );
                            
                            if(phoneNumber=="")
                            {
                                $("#ovoPhoneNumber").addClass("is-invalid");
                                
                                $("#text-alert").hide();
                                $("#text-alert").html( "" );
                                $("#ovoPhoneNumber").attr("disabled", false);
                                $("#submit").attr("disabled", false);
                                $("#submit").html(\' <strong>Click to pay with <img class="ml-2 mr-2" src="'.config('site.assets').'/img/payment/ovo-light.png" height="30" /></strong> \');
                                return false;
                            }


                            $.ajax({
                                data: {
                                    "sessionId": \''.$sessionId.'\',
                                    "phoneNumber": phoneNumber,
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/ovo\'
                                }).done(function(data) {
                                    
                                    if(data.message=="success")
                                    {
                                        window.startListenerEwallet(data.reference_id);
                                        setTimeout(
                                        function() 
                                        {
                                            window.stopListenerEwallet(data.reference_id);
                                            failedpaymentEwallet("ovo");
                                        }, 65000);
                                    }
                                    else
                                    {
                                        failedpaymentEwallet("ovo");
                                    }

                                }).fail(function(error) {

                                    failedpaymentEwallet("ovo");
                                        
                            });

                            return false;
            }
        ';
        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function xendit_jscript($sessionId)
    {
        $shoppingcart = Cache::get('_'. $sessionId);
        $amount = BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,'IDR');
        
        $jscript = '

        $("#submitCheckout").slideUp("slow");

        $("#paymentContainer").html(\'<form id="payment-form"><div class="row mt-4"><div class="col-md-12 mb-2"><strong>Card Information</strong></div><div class="col-md-12 mb-2"><div class="input-group"><div class="input-group-append"><span class="input-group-text" id="inputGroupPrepend3"><i id="cardBrand" class="far fa-credit-card"></i></span></div><input class="form-control" type="text" id="card-number" placeholder="Card Number" value="" style="height: 47px;"><div id="cardNumberFeddback" class="invalid-feedback">Card number invalid.</div></div></div></div><div class="row"><div class="col-md-6 mb-2"><input type="text" class="form-control" id="cc-expiration" placeholder="MM / YY" required="" style="height: 47px;"><div id="expirationFeddback" class="invalid-feedback">Expiration invalid.</div></div><div class="col-md-6 mb-2"><input type="text" class="form-control" id="cc-cvv" placeholder="CVC" required="" style="height: 47px;"><div id="cvvFeedback" class="invalid-feedback">CVC invalid.</div></div></div><button style="height:47px;" class="mt-2 btn btn-lg btn-block btn-theme" id="submit"><strong>Pay with card</strong></button></form><div id=\"loader\" class=\"mb-4\"></div><div id=\"text-alert\" class=\"text-center\"></div><div id="three-ds-container" class="modal" style="display: none;"></div>\');

        payform.cardNumberInput(document.getElementById("card-number"));
        payform.expiryInput(document.getElementById("cc-expiration"));
        payform.cvcInput(document.getElementById("cc-cvv"));

        $(\'#card-number\').on(\'input\', function() {
            if($(\'#card-number\').val().length >=3)
            {
                var card_brand = payform.parseCardType($(\'#card-number\').val());
                if(card_brand=="visa")
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'fab\').addClass(\'fa-cc-visa\');
                }
                else if(card_brand=="mastercard")
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'fab\').addClass(\'fa-cc-mastercard\');
                }
                else if(card_brand=="jcb")
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'fab\').addClass(\'fa-cc-jcb\');
                }
                else
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'far\').addClass(\'fa-credit-card\');
                }
            }
            
            
        });

        function xenditResponseHandler (err, creditCardToken) {
            if (creditCardToken.status === "APPROVED" || creditCardToken.status === "VERIFIED") {
                            $("#three-ds-container").hide();
                            $("#loader").show();
                            $("#payment-form").slideUp("slow");  
                            $("#proses").hide();
                            $("#loader").addClass("loader");
                            $("#text-alert").show();
                            $("#text-alert").prepend( "Please wait and do not close the browser or refresh the page" );
                            
                            $.ajax({
                                beforeSend: function(request) {
                                    request.setRequestHeader(\'sessionId\', \''. $shoppingcart->session_id .'\');
                                    request.setRequestHeader(\'tokenId\', creditCardToken.id);
                                },
                                type: \'POST\',
                                url: \''. env('APP_API_URL') .'/payment/xendit\'
                            }).done(function( data ) {
                                if(data.id=="1")
                                {
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000);
                                    
                                }
                                else
                                {
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $("#payment-form").slideDown("slow");
                                    enableButton();
                                    $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><strong style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> \'+ data.message +\'</strong></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                }
                            });
            } else if (creditCardToken.status === "IN_REVIEW") {
                            $("#three-ds-container").hide();
                            $("#three-ds-container").html("<iframe id=\"3ds-inline-frame\" name=\"3ds-inline-frame\" scrolling=\"no\"></iframe>");
                            $("#3ds-inline-frame").css("background-color", "#FFFFFF");
                            $("#3ds-inline-frame").css("top", "0px");
                            $("#3ds-inline-frame").css("left", "0px");
                            $("#3ds-inline-frame").css("width", "100%");
                            $("#3ds-inline-frame").css("height", "100%");
                            $("#3ds-inline-frame").css("position", "absolute");
                            window.open(creditCardToken.payer_authentication_url, "3ds-inline-frame");
                            $("#three-ds-container").show();
            } else if (creditCardToken.status === "FRAUD") {
                            enableButton();
            } else if (creditCardToken.status === "FAILED") {
                            enableButton();
            }
        }
        
        function cleanFeedback()
        {
            $("#card-number").removeClass("is-invalid");
            $("#cc-expiration").removeClass("is-invalid");
            $("#cc-cvv").removeClass("is-invalid");
        }

        function enableButton()
        {
            $("#three-ds-container").hide();
            $("#card-number").attr("disabled", false);
            $("#cc-expiration").attr("disabled", false);
            $("#cc-cvv").attr("disabled", false);
            $("#loader").hide();
            $("#loader").removeClass("loader");
            $("#payment-form").slideDown("slow");
            $("#submit").attr("disabled", false);
            $("#submit").html(\'<strong>Pay with card</strong>\');
        }

        var form = document.getElementById(\'payment-form\');
        form.addEventListener(\'submit\', function(ev) {

                ev.preventDefault();
                
                cleanFeedback();
                
                $("#alert-payment").slideUp("slow");
                $("#submit").attr("disabled", true);
                $("#submit").html(\' <i class="fa fa-spinner fa-spin fa-fw"></i>  processing... \');

                Xendit.setPublishableKey("'. env("XENDIT_PUBLIC_KEY") .'");
                
                var cardNumber = $("#card-number").val();
                var expiry = $("#cc-expiration").val();
                var expiryArray = expiry.split("/");
                var expiryMonth = expiryArray[0].trim();
                var expiryYear = expiryArray[1].trim();
                var cvvNumber = $("#cc-cvv").val();

                
                if(expiryYear.length==2)
                {
                    expiryYear = "'. substr(date('Y'),0,2) .'"+ expiryYear;
                }

                if(!payform.validateCardNumber(cardNumber))
                {
                    $("#card-number").addClass("is-invalid");
                    enableButton();
                    return false;
                }
            
                if(!payform.validateCardExpiry(expiryMonth,expiryYear))
                {
                    $("#cc-expiration").addClass("is-invalid");
                    enableButton();
                    return false;
                }

                if(!payform.validateCardCVC(cvvNumber))
                {
                    $("#cc-cvv").addClass("is-invalid");
                    enableButton();
                    return false;
                }


                $("#card-number").attr("disabled", true);
                $("#cc-expiration").attr("disabled", true);
                $("#cc-cvv").attr("disabled", true);

                cardNumber = cardNumber.replace(/\s/g,"");
                expiryMonth = expiryMonth.trim();
                expiryYear = expiryYear.trim();
                cvvNumber = cvvNumber.trim();
                
                Xendit.card.createToken({
                    amount: '.$amount.',
                    card_number: cardNumber,
                    card_exp_month: expiryMonth,
                    card_exp_year: expiryYear,
                    card_cvn: cvvNumber,
                    is_multiple_use: false
                }, xenditResponseHandler);


                return false;
            });
        

        ';
        return response($jscript)->header('Content-Type', 'application/javascript');
    }

    public function stripe_jscript($sessionId)
    {
        
        
        $shoppingcart = Cache::get('_'. $sessionId);
        $amount = BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,'USD');
        $amount = $amount * 100;
        $jscript = '
        
            $("#submitCheckout").slideUp("slow");
            $("#paymentContainer").html(\'<form id="payment-form"><div id="stripe-wallet" class="pt-2 pb-2 justify-content-center"><h2>Pay with</h2><div id="payment-request-button"></div><div class="mt-2 mb-2" style="width: 100%; height: 12px; border-bottom: 1px solid #D0D0D0; text-align: center"><span style="color: #D0D0D0; font-size: 12px; background-color: #FFFFFF; padding: 0 10px;">or pay with card</span></div></div><div class="form-control mt-2 mb-2" style="height:47px;" id="card-element"></div><div id="card-errors" role="alert"></div><button style="height:47px;" class="btn btn-lg btn-block btn-theme" id="submit"><strong>Pay with card</strong></button></form><div id=\"loader\" class=\"mb-4\"></div><div id=\"text-alert\" class=\"text-center\"></div>\');

                 var stripe = Stripe(\''. env("STRIPE_PUBLISHABLE_KEY") .'\', {
                    apiVersion: "2020-08-27",
                 });

                 var paymentRequest = stripe.paymentRequest({
                    country: \'US\',
                    currency: \'usd\',
                    total: {
                        label: \''. env('APP_NAME') .'\',
                        amount: '. $amount .',
                    },
                    requestPayerName: true,
                    requestPayerEmail: true,
                 });
                 
                 var elements = stripe.elements();

                 var prButton = elements.create(\'paymentRequestButton\', {
                    paymentRequest: paymentRequest,
                 });

                 paymentRequest.canMakePayment().then(function(result) {
                    if (result) {
                        prButton.mount(\'#payment-request-button\');
                    } else {
                        document.getElementById(\'stripe-wallet\').style.display = \'none\';
                    }
                 });
                 
                 var style = {
                    base: {
                        color: "#32325d",
                        fontSize: "16px",
                        lineHeight: "34px"
                    }
                 };

                 var card = elements.create("card", { style: style });
                 card.mount("#card-element");

                var form = document.getElementById(\'payment-form\');
                form.addEventListener(\'submit\', function(ev) {
                   
                    ev.preventDefault();

                    $("#alert-payment").slideUp("slow");
                    $("#submit").attr("disabled", true);
                    $("#submit").html(\' <i class="fa fa-spinner fa-spin fa-fw"></i>  processing... \');

                    $.ajax({
                    beforeSend: function(request) {
                        request.setRequestHeader(\'sessionId\', \''. $shoppingcart->session_id .'\');
                    },
                    type: \'POST\',
                    url: \''. env('APP_API_URL') .'/payment/stripe\'
                }).done(function( data ) {
                    
                    $("#loader").show();
                    $("#payment-form").slideUp("slow");  
                    $("#proses").hide();
                    $("#loader").addClass("loader");
                    $("#text-alert").show();
                    $("#text-alert").prepend( "Please wait and do not close the browser or refresh the page" );

                    stripe.confirmCardPayment(data.intent.client_secret, {
                        payment_method: {
                            card: card
                        }
                    }).then(function(result) {

                        if (result.error) {

                            $("#text-alert").hide();
                            $("#text-alert").empty();
                            $("#loader").hide();
                            $("#loader").removeClass("loader");
                            $("#payment-form").slideDown("slow");
                            $("#submit").attr("disabled", false);
                            $("#submit").html(\'<strong>Pay with card</strong>\');
                            $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> \'+ result.error.message +\'</h2></div>\');
                            $(\'#alert-payment\').fadeIn("slow");

                        } else {
                            
                            if (result.paymentIntent.status === \'succeeded\' || result.paymentIntent.status === \'requires_capture\') {
                                
                                $.ajax({
                                data: {
                                    "authorizationID": result.paymentIntent.id,
                                    "sessionId": \''.$sessionId.'\',
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/stripe/confirm\'
                                }).done(function(data) {
                                if(data.id=="1")
                                {
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000); 
                                }

                                }).fail(function(error) {
                                    
                                });

                            }
                        }
                    });
                });


                });



                paymentRequest.on(\'paymentmethod\', async(e) => {

                    const {intent} = await fetch("'. env('APP_API_URL') .'/payment/stripe", {
                        method: "POST",
                        credentials: \'same-origin\',
                        headers: {
                            "sessionId" : "'. $shoppingcart->session_id .'",
                        },
                    }).then(r => r.json());
                    
                    const {error,paymentIntent} = await stripe.confirmCardPayment(intent.client_secret,{
                            payment_method: e.paymentMethod.id
                        }, {handleActions:false});
                    
                    $("#payment-form").slideUp("slow");  
                    $("#proses").hide();
                    $("#loader").addClass("loader");
                    $("#text-alert").show();
                    $("#text-alert").prepend( "Please wait and do not close the browser or refresh the page" );

                    if(error) {
                        e.complete("fail");
                        $("#text-alert").hide();
                        $("#text-alert").empty();
                        $("#loader").hide();
                        $("#loader").removeClass("loader");
                        $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Payment Failed</h2></div>\');
                        $(\'#alert-payment\').fadeIn("slow");
                    }

                    e.complete("success");

                    if(paymentIntent.status == "requires_action")
                    {
                        stripe.confirmCardPayment(intent.client_secret).then(function(result){
                            if(result.error)
                            {
                                // failed
                                e.complete("fail");
                                $("#text-alert").hide();
                                $("#text-alert").empty();
                                $("#loader").hide();
                                $("#loader").removeClass("loader");
                                $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Payment Failed</h2></div>\');
                                $(\'#alert-payment\').fadeIn("slow");
                            }
                            else
                            {
                                // success
                                $.ajax({
                                data: {
                                    "authorizationID": paymentIntent.id,
                                    "sessionId": \''.$sessionId.'\',
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/stripe/confirm\'
                                }).done(function(data) {
                                if(data.id=="1")
                                {
                                    e.complete("success");
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000); 
                                }

                                }).fail(function(error) {
                                    
                                });
                            }
                        });
                        
                    } else {
                                // success
                                $.ajax({
                                data: {
                                    "authorizationID": paymentIntent.id,
                                    "sessionId": \''.$sessionId.'\',
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/stripe/confirm\'
                                }).done(function(data) {
                                if(data.id=="1")
                                {
                                    e.complete("success");
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000); 
                                }

                                }).fail(function(error) {
                                    
                                });
                    }

                });
            

        ';
        return response($jscript)->header('Content-Type', 'application/javascript');
    }


    public function paypal_jscript($sessionId)
    {
        
        $jscript = '
        jQuery(document).ready(function($) {

            $("#submitCheckout").slideUp("slow");  
            $("#paymentContainer").html(\'<div id="proses"><div id="paypal-button-container"></div></div><div id=\"loader\" class=\"mb-4\"></div><div id=\"text-alert\" class=\"text-center\"></div>\');
           
            paypal.Buttons({
                style: {
                    layout: "horizontal",
                    color: "gold",
                    label: "pay",
                    tagline: false
                },
                createOrder: function() {
                    return fetch(\''. url('/api') .'/payment/paypal\', {
                        method: \'POST\',
                        credentials: \'same-origin\',
                        headers: {
                            \'sessionId\': \''.$sessionId.'\'
                            }
                    }).then(function(res) {
                            return res.json();
                    }).then(function(data) {
                            return data.result.id;
                    });
                },
                onError: function (err) {
                    $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Payment Error!</h2></div>\');
                    $(\'#alert-payment\').fadeIn("slow");
                },
                onApprove: function(data, actions) {
                    
                    $("#proses").hide();
                    $("#loader").addClass("loader");
                    $("#text-alert").prepend( "Please wait and do not close the browser or refresh the page" );

                    actions.order.capture().then(function(orderData) {
                            
                            $.ajax({
                                data: {
                                    "orderID": data.orderID,
                                    "sessionId": \''.$sessionId.'\',
                                },
                                type: \'POST\',
                                url: \''. url('/api') .'/payment/paypal/confirm\'
                            }).done(function(data) {
                                if(data.id=="1")
                                {
                                    $("#text-alert").hide();
                                    $("#text-alert").empty();
                                    $("#loader").hide();
                                    $("#loader").removeClass("loader");
                                    $(\'#alert-payment\').html(\'<div id="alert-success" class="alert alert-primary text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-smile"></i> Payment Successful!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                    setTimeout(function (){
                                        window.openAppRoute(data.message); 
                                    }, 1000);
                                }
                                else
                                {
                                    $("#loader").hide();
                                    $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> Payment Failed!</h2></div>\');
                                    $(\'#alert-payment\').fadeIn("slow");
                                }
                            }).fail(function(error) {
                                
                            });

                    });
                }
            }).render(\'#paypal-button-container\');
        });';
        
        return response($jscript)->header('Content-Type', 'application/javascript');
    }


}
