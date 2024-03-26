<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\PaymentHelper;
use budisteikul\toursdk\Helpers\XenditHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use budisteikul\toursdk\Helpers\FirebaseHelper;
use Ramsey\Uuid\Uuid;
use budisteikul\toursdk\Helpers\LogHelper;

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
            $trackingCode = $data['trackingCode'];
            
            $check_question = BookingHelper::check_question_json($sessionId,$data);
            if(count($check_question) > 0)
            {
                $check_question['message'] = '<span style="font-size:16px">Oops there was a problem, please check your input and try again.</span>';
                return response()->json($check_question);
            }

            $shoppingcart = BookingHelper::save_question_json($sessionId,$data);
            $shoppingcart = BookingHelper::save_trackingCode($sessionId,$trackingCode);


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

                case 'qris':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'qris'
                    ]);
                break;

                case 'bss':
                    return response()->json([
                        'message' => 'success',
                        'payment' => 'bss'
                    ]);
                break;

                default:
                    return response()->json([
                        'id' => "0",
                        'message' => "Oops there was a problem",
                    ]);
            }
            
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


    public function qris_jscript($sessionId)
    {
        $shoppingcart = Cache::get('_'. $sessionId);
        BookingHelper::set_bookingStatus($sessionId,'PENDING');
        BookingHelper::set_confirmationCode($sessionId);
        $response = PaymentHelper::create_payment($sessionId,"xendit","qris");

        if($response->status->id=="1")
        {
            $shoppingcart = BookingHelper::confirm_booking($sessionId);
            $session_id = $shoppingcart->session_id;
            $confirmation_code = $shoppingcart->confirmation_code;
            $redirect = '/booking/receipt/'.$session_id.'/'.$confirmation_code;
            $jscript = '
                afterCheckout("'.$redirect.'");
            ';
            return response($jscript)->header('Content-Type', 'application/javascript');
        }
        else
        {
            $jscript = '
                $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><strong style="margin-bottom:10px; margin-top:10px; font-size:16px;"><i class="far fa-frown"></i> Oops there was a problem</strong></div>\');
                $(\'#alert-payment\').fadeIn("slow");
                setTimeout(function (){
                    changePaymentMethod();
                }, 1500);
            ';
            return response($jscript)->header('Content-Type', 'application/javascript');
        }
    }

    

    public function xendit_jscript($sessionId)
    {
        $shoppingcart = Cache::get('_'. $sessionId);
        $first_name = BookingHelper::get_answer($shoppingcart,'firstName');
        $last_name = BookingHelper::get_answer($shoppingcart,'lastName');
        $amount = BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,'IDR');
        
        $billing_form = '
            <div id="billing_form1" class="row no-gutters mt-2">
                <div class="col-md-12 mb-2">
                    <h2 class=" mt-2">Billing Information</h2>
                </div>
                <div class="col-md-6 mb-2 pr-1">
                    <label for="cc-givenName"><strong>First name</strong></label>
                    <input value="'.$first_name.'" type="text" class="form-control" id="cc-givenName" required="" placeholder="Given Name" style="height: 47px;border-radius: 0;">
                    <div id="givenNameFeedback" class="invalid-feedback">
                        Invalid value
                    </div>
                </div>
                <div class="col-md-6 mb-2 pr-1">
                    <label for="cc-surname"><strong>Last name</strong></label>
                    <input value="'.$last_name.'" type="text" class="form-control" id="cc-surname" required="" placeholder="Last Name"  style="height: 47px;border-radius: 0;">
                    <div id="lastNameFeedback" class="invalid-feedback">
                        Invalid value
                    </div>
                </div>
                <div class="col-md-12 mb-2 pr-1">
                    <label for="cc-streetLine1"><strong>Street line 1</strong></label>
                    <input type="text" class="form-control" id="cc-streetLine1" required="" placeholder="Address" style="height: 47px;border-radius: 0;">
                    <div id="streetLineFeedback" class="invalid-feedback">
                        Invalid value
                    </div>
                </div>
                <div class="col-md-12 mb-2 pr-1">
                    <label for="cc-postalCode"><strong>Postal code</strong></label>
                    <input type="text" class="form-control" id="cc-postalCode" required="" placeholder="Postal code" style="height: 47px;border-radius: 0;">
                    <div id="zipCodeFeedback" class="invalid-feedback">
                        Invalid value
                    </div>
                </div>
            </div>

        ';


        $payment_container = '
        <hr />
        <form id="payment-form">

            <div class="row">
                <div class="col-md-12 mb-2">
                    <h2 class=" mt-2">Card Information</h2>
                </div>
                <div class="col-md-12 mb-2">
                    <label for="card-number"><strong>Card number</strong></label>
                    <div class="input-group pr-1">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="far fa-credit-card fa-lg fa-fw"></i>
                            </span>
                        </div>
                        <input class="form-control" type="text" id="card-number" placeholder="Card number" value="" style="height: 47px;border-radius: 0;" onKeyUp="return checkCardNumber();">
                        <div id="cardNumberFeedback" class="invalid-feedback">
                            Invalid value
                        </div>
                    </div>
                </div>
            </div>

            <div class="row no-gutters">
                <div class="col-md-6 mb-2">
                    <label for="cc-expiration"><strong>Valid thru</strong></label>
                    <div class="input-group pr-1">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="far fa-calendar fa-lg fa-fw"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" id="cc-expiration" placeholder="MM / YY" required="" style="height: 47px;border-radius: 0;" onKeyUp="return checkExpiration();">
                        <div id="expirationFeedback" class="invalid-feedback">
                            Invalid value
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <label for="cc-cvv"><strong>CVV / CVN / CVC</strong></label>
                    <div class="input-group pr-1">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-key fa-lg fa-fw"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" id="cc-cvv" placeholder="3-4 digits code" required="" style="height: 47px;border-radius: 0;" onKeyUp="return checkCvv();">
                        <div id="cvvFeedback" class="invalid-feedback">
                            Invalid value
                        </div>
                    </div>
                </div>
            </div>

            <div id="billing_form"></div>
            

            <button style="height:47px;" class="mt-2 btn btn-lg btn-block btn-theme" id="submit">
                <i class="fas fa-lock"></i> <strong>Pay with card</strong>
            </button>

            <div id="change_payment" class="mt-2">
                <center>
                    <small><a href="#paymentMethod" class="text-theme" onClick="changePaymentMethod();">Click here</a> to change payment method</small>
                </center>
            </div>

        </form>

        <div id="loader" class="mb-4"></div>
        <div id="text-alert" class="text-center"></div>
        <div id="three-ds-container" class="modal" style="display: none;"></div>
        ';

        

        $jscript = '


        $("#submitCheckout").slideUp("slow");
        $("#paymentContainer").html(\''. str_replace(array("\r", "\n"), '', $payment_container) .'\');
        

        payform.cardNumberInput(document.getElementById("card-number"));
        payform.expiryInput(document.getElementById("cc-expiration"));
        payform.cvcInput(document.getElementById("cc-cvv"));

        $(\'#card-number\').on(\'input\', function() {
            if($(\'#card-number\').val().length >=8)
            {
                var card_brand = payform.parseCardType($(\'#card-number\').val());
                if(card_brand=="visa")
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'fab\').addClass(\'fa-cc-visa  fa-lg\');
                }
                else if(card_brand=="mastercard")
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'fab\').addClass(\'fa-cc-mastercard  fa-lg\');
                }
                else if(card_brand=="jcb")
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'fab\').addClass(\'fa-cc-jcb  fa-lg\');
                }
                else
                {
                    $("#cardBrand").removeClass();
                    $("#cardBrand").addClass(\'far\').addClass(\'fa-credit-card  fa-lg\');
                }
            }
            else
            {
                $("#cardBrand").removeClass();
                $("#cardBrand").addClass(\'far\').addClass(\'fa-credit-card  fa-lg\');
            }
        });

        function xenditResponseHandler (err, creditCardToken) {
            if (err) {
                enableButton();
                var error_message = err.message;
                $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> \'+ error_message +\'</h2></div>\');
                $(\'#alert-payment\').fadeIn("slow");
                return;
            }

            if (creditCardToken.status === "APPROVED" || creditCardToken.status === "VERIFIED") {
                            $("#three-ds-container").hide();
                            $("#loader").show();
                            $("#payment-form").slideUp("slow");  
                            $("#proses").hide();
                            $("#loader").addClass("loader");
                            $("#text-alert").show();
                            $("#text-alert").prepend( "Please wait and do not close the browser or refresh the page" );
                            
                            postBilling(creditCardToken.id,creditCardToken.card_info.country);

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
                                        afterCheckout(data.message);
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
                            var error_message = creditCardToken.failure_reason;

                            if(creditCardToken.failure_reason=="AUTHENTICATION_FAILED")
                            {
                                error_message = "Authentication Failed";
                            }
                            
                            $(\'#alert-payment\').html(\'<div id="alert-failed" class="alert alert-danger text-center mt-2" role="alert"><h2 style="margin-bottom:10px; margin-top:10px;"><i class="far fa-frown"></i> \'+ error_message +\'</h2></div>\');
                            $(\'#alert-payment\').fadeIn("slow");
                           
            }
        }
        
        function randomNumber()
        {
            var randomNumber = Date.now() + Math.random();
            randomNumber = randomNumber.toString().replace(".","");
            return randomNumber;
        }

        function cleanFeedback()
        {
            $("#card-number").removeClass("is-invalid");
            $("#cc-expiration").removeClass("is-invalid");
            $("#cc-cvv").removeClass("is-invalid");
        }

        
        function addBillingForm()
        {
            $("#billing_form").html(\''. str_replace(array("\r", "\n"), '', $billing_form) .'\');
            
        }
        function removeBillingForm()
        {
            $("#billing_form").html(\'\');
            
        }

        

        var cardNumber_keypress = false;
        var expiration_keypress = false;
        var cvv_keypress = false;
        var oldBin = "";
        $("#card-number").on("blur", function() {
            var cardNumber = $("#card-number").val();
            cardNumber_keypress = true;
            if(!payform.validateCardNumber(cardNumber))
            {
                $("#card-number").addClass("is-invalid");
                return;
            }
            else
            {
                $("#card-number").removeClass("is-invalid");
                cardNumber_keypress = false;
            }
            if (oldBin != this.value) {
                checkBin();
                oldBin = this.value;
            }
        });
        
        function checkCardNumber()
        {
            if(cardNumber_keypress)
            {
                var cardNumber = $("#card-number").val();
                if(!payform.validateCardNumber(cardNumber))
                {
                    $("#card-number").addClass("is-invalid");
                }
                else
                {
                    $("#card-number").removeClass("is-invalid");
                    if (oldBin != this.value) {
                        checkBin();
                        oldBin = this.value;
                    }
                }
            }
        }

        $("#cc-expiration").on("blur", function() {
            var expiry = $("#cc-expiration").val();
            expiration_keypress = true;
            var expiryArray = expiry.split("/");
            if(expiryArray.length>1)
            {
                
                var expiryMonth = expiryArray[0].trim();
                var expiryYear = expiryArray[1].trim();
                
                if(!payform.validateCardExpiry(expiryMonth,expiryYear))
                {
                    $("#cc-expiration").addClass("is-invalid");
                }
                else
                {
                    $("#cc-expiration").removeClass("is-invalid");
                    expiration_keypress = false;
                }
            }
            else
            {
                $("#cc-expiration").addClass("is-invalid");
            }
        });

        function checkExpiration()
        {
            if(expiration_keypress)
            {
                var expiry = $("#cc-expiration").val();
                var expiryArray = expiry.split("/");
                if(expiryArray.length>1)
                {  
                    var expiryMonth = expiryArray[0].trim();
                    var expiryYear = expiryArray[1].trim();
                    if(!payform.validateCardExpiry(expiryMonth,expiryYear))
                    {
                        $("#cc-expiration").addClass("is-invalid");
                    }
                    else
                    {
                        $("#cc-expiration").removeClass("is-invalid");
                    }
                }
                else
                {
                    $("#cc-expiration").addClass("is-invalid");
                }
            }
        }

        $("#cc-cvv").on("blur", function() {
            var cvvNumber = $("#cc-cvv").val();
            cvv_keypress = true;
            if(!payform.validateCardCVC(cvvNumber))
            {
                $("#cc-cvv").addClass("is-invalid");
            }
            else
            {
                $("#cc-cvv").removeClass("is-invalid");
                cvv_keypress = false;
            }
        });

        function checkCvv()
        {
            if(cvv_keypress)
            {
                var cvvNumber = $("#cc-cvv").val();
                if(!payform.validateCardCVC(cvvNumber))
                {
                    $("#cc-cvv").addClass("is-invalid");
                }
                else
                {
                    $("#cc-cvv").removeClass("is-invalid");
                }
            }
        }




        function checkBin()
        {
            var cardNumber = $("#card-number").val();
            cardNumber = cardNumber.replace(/\s/g,"").trim();
            var bin = cardNumber.substring(0, 8);

            if(bin.length!=8)
            {
                removeBillingForm();
                return false;
            }

            $.ajax({
                    url: "'. env('APP_API_URL') .'/tool/bin",
                    method: "POST",
                    data: { 
                        bin: bin
                    },
                }).done(function(response) {
                    var country_code = response.country_code;
                    if(country_code == "US" || country_code == "CA" || country_code == "GB")
                    {
                        addBillingForm();
                    }
                    else
                    {
                        removeBillingForm();
                    }
                }).fail(function( jqXHR, textStatus ) {

                });

            
        }



        function postBilling(id,country)
        {
            if($("#billing_form1").length==1)
            {
                var givenName = $("#cc-givenName").val().trim();
                var surname = $("#cc-surname").val().trim();
                var streetLine1 = $("#cc-streetLine1").val().trim();
                var postalCode = $("#cc-postalCode").val().trim();

                $.ajax({
                    url: "'. env('APP_API_URL') .'/tool/billing/"+ id,
                    method: "POST",
                    data: { 
                        tokenId: id,
                        givenName: givenName,
                        surname: surname,
                        streetLine1: streetLine1,
                        postalCode: postalCode,
                        country: country
                    },
                }).done(function(response) {
  
                }).fail(function( jqXHR, textStatus ) {

                });
            }
            
        }

        function enableButton()
        {
            $("#three-ds-container").hide();
            $("#card-number").attr("disabled", false);
            $("#cc-expiration").attr("disabled", false);
            $("#cc-cvv").attr("disabled", false);
            $("#cc-givenName").attr("disabled", false);
            $("#cc-surname").attr("disabled", false);
            $("#cc-streetLine1").attr("disabled", false);
            $("#cc-postalCode").attr("disabled", false);
            $("#loader").hide();
            $("#loader").removeClass("loader");
            $("#payment-form").slideDown("slow");
            $("#submit").attr("disabled", false);
            $("#submit").html(\'<i class="fas fa-lock"></i> <strong>Pay with card</strong>\');
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

                var external_id = randomNumber();
                
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

                $("#cc-givenName").attr("disabled", true);
                $("#cc-surname").attr("disabled", true);
                $("#cc-streetLine1").attr("disabled", true);
                $("#cc-postalCode").attr("disabled", true);

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
                    is_multiple_use: false,
                    external_id: external_id
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

        $payment_container = '<hr />
        <form id="payment-form">
            <div id="stripe-wallet" class="pt-2 pb-2 justify-content-center">
                <h2>Pay with</h2>
                <div id="payment-request-button"></div>
                <div class="mt-2 mb-2" style="width: 100%; height: 12px; border-bottom: 1px solid #D0D0D0; text-align: center">
                    <span style="color: #D0D0D0; font-size: 12px; background-color: #FFFFFF; padding: 0 10px;">or pay with card</span>
                </div>
            </div>
            <div class="form-control mt-2 mb-2" style="height:47px;" id="card-element"></div>
            <div id="card-errors" role="alert"></div>
            <button style="height:47px;" class="btn btn-lg btn-block btn-theme" id="submit">
                <i class="fas fa-lock"></i> <strong>Pay with card</strong>
            </button>
            <div id="change_payment" class="mt-2">
                <center><small><a href="#paymentMethod" class="text-theme" onClick="changePaymentMethod();">Click here</a> to change payment method</small>
                </center>
            </div>
        </form>

        <div id="loader" class="mb-4"></div>
        <div id="text-alert" class="text-center"></div>
        ';
        

        $jscript = '
        
            $("#submitCheckout").slideUp("slow");
            $("#paymentContainer").html(\''.str_replace(array("\r", "\n"), '', $payment_container).'\');

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
                            $("#submit").html(\'<i class="fas fa-lock"></i> <strong>Pay with card</strong>\');
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
                                        afterCheckout(data.message);
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
                                        afterCheckout(data.message);
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
                                        afterCheckout(data.message);
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
        
        $payment_container = '<hr />
        <div id="proses">
            <div id="paypal-button-container"></div>
            <div id="change_payment" class="mt-2">
                <center><small><a href="#paymentMethod" class="text-theme" onClick="changePaymentMethod();">Click here</a> to change payment method</small></center>
            </div>
        </div>

        <div id="loader" class="mb-4"></div>
        <div id="text-alert" class="text-center"></div>
        ';
        

        $jscript = '
        jQuery(document).ready(function($) {

            $("#submitCheckout").slideUp("slow");  
            $("#paymentContainer").html(\''. str_replace(array("\r", "\n"), '', $payment_container) .'\');
           
            paypal.Buttons({
                /*
                style: {
                    layout: "horizontal",
                    color: "gold",
                    label: "pay",
                    tagline: false
                },
                */
                createOrder: function() {
                    $("#alert-payment").html(\'\');
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
                                        afterCheckout(data.message);
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
