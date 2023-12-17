<?php
namespace budisteikul\toursdk\Helpers;

use budisteikul\toursdk\Helpers\PaypalHelper;
use budisteikul\toursdk\Helpers\XenditHelper;
use budisteikul\toursdk\Helpers\StripeHelper;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\GeneralHelper;
use budisteikul\toursdk\Helpers\SettingHelper;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PaymentHelper {
	
    public static function confirm_payment($shoppingcart,$status,$force=false)
    {
        if($force)
        {
            $shoppingcart->booking_status = "PENDING";
            $shoppingcart->save();
        }

        if($status=="CONFIRMED")
        {
            if($shoppingcart->booking_status=="PENDING")
            {
                $shoppingcart->booking_status = 'CONFIRMED';
                $shoppingcart->save();
                $shoppingcart->shoppingcart_payment->payment_status = 2;
                $shoppingcart->shoppingcart_payment->save();
            }
        }

        if($status=="PENDING")
        {
            if($shoppingcart->booking_status!="PENDING")
            {
                $shoppingcart->booking_status = 'PENDING';
                $shoppingcart->save();
                $shoppingcart->shoppingcart_payment->payment_status = 4;
                $shoppingcart->shoppingcart_payment->save();
            }
        }

        if($status=="CANCELED")
        {
            if($shoppingcart->booking_status=="PENDING")
            {
                $shoppingcart->booking_status = 'CANCELED';
                $shoppingcart->save();
                $shoppingcart->shoppingcart_payment->payment_status = 3;
                $shoppingcart->shoppingcart_payment->save();
            }

            if($shoppingcart->booking_status=="CONFIRMED")
            {
                if($shoppingcart->shoppingcart_payment->payment_status==2)
                {
                    $shoppingcart->booking_status = 'CANCELED';
                    $shoppingcart->save();
                    $shoppingcart->shoppingcart_payment->payment_status = 5;
                    $shoppingcart->shoppingcart_payment->save();
                }
                else
                {
                    $shoppingcart->booking_status = 'CANCELED';
                    $shoppingcart->save();
                    $shoppingcart->shoppingcart_payment->payment_status = 3;
                    $shoppingcart->shoppingcart_payment->save();
                }
            }
        }

        return $shoppingcart;
        
    }
    
    public static function set_paymentStatus($sessionId,$payment_status='')
    {
        $shoppingcart = Cache::get('_'. $sessionId);
        $shoppingcart->payment->payment_status = $payment_status;
        Cache::forget('_'. $sessionId);
        Cache::add('_'. $sessionId, $shoppingcart, 172800);
        return $shoppingcart;
    }

    public static function have_payment($shoppingcart)
    {
        $status = false;
        if(isset($shoppingcart->shoppingcart_payment))
        {
            $status = true;
        }
        return $status;
    }

    public static function get_paymentStatus($shoppingcart)
    {
        if(self::have_payment($shoppingcart))
        {
            $asset = SettingHelper::getSetting('assets');

            if($shoppingcart->shoppingcart_payment->payment_provider=="none")
            {
                switch($shoppingcart->shoppingcart_payment->payment_status)
                {
                    
                    case 2:
                        return '<div class="card mb-4">
                                <span class="badge badge-success invoice-color-success" style="font-size:20px;"><i class="fa fa-check-circle" aria-hidden="true"></i> PAID </span>
                                </div>';
                    case 4:
                        return '<div class="card mb-4">
                                <span class="badge badge-info invoice-color-info" style="font-size:20px; ">
                                Waiting for payment</span>
                                </div>';
                    default:
                        return '<div class="card mb-4">
                            <span class="badge badge-danger invoice-color-danger" style="font-size:20px;"><i class="fa fa-times-circle" aria-hidden="true"></i>
 INVOICE CANCELED</span>
                            </div>';

                }
                
            }


            $text = '';
            if($shoppingcart->shoppingcart_payment->rate_from!=$shoppingcart->shoppingcart_payment->rate_to)
            {
                $text .= '<b>Total :</b> '.$shoppingcart->shoppingcart_payment->currency.' '. GeneralHelper::numberFormat($shoppingcart->shoppingcart_payment->amount,$shoppingcart->shoppingcart_payment->currency) .'<br />';
                $text .= '<b>Rate :</b> '. BookingHelper::get_rate($shoppingcart) .'<br />';
                $text = '<div class="card-body bg-light">'. $text .'</div>';
            }

            switch($shoppingcart->shoppingcart_payment->payment_status)
            {
                case 1:
                    return '
                                <div class="card mb-4">
                                <span class="badge badge-success invoice-color-success" style="font-size:20px;"><i class="fa fa-info-circle" aria-hidden="true"></i> AUTHORIZED </span>
                                '. $text .'
                                </div>';
                break;
                case 2:
                    return '
                                <div class="card mb-4">
                                <span class="badge badge-success invoice-color-success" style="font-size:20px;"><i class="fa fa-check-circle" aria-hidden="true"></i> PAID </span>
                                '. $text .'
                                </div>';
                break;
                case 3:
                    return '
                                <div class="card mb-4">
                                <span class="badge badge-danger invoice-color-danger" style="font-size:20px;"><i class="fa fa-times-circle" aria-hidden="true"></i>
 UNPAID </span>
                                '. $text .'
                                </div>';

                break;
                case 5:
                    return '
                                <div class="card mb-4">
                                <span class="badge badge-danger invoice-color-danger" style="font-size:20px;"><i class="fas fa-sync-alt"></i>
 REFUNDED </span>
                                '. $text .'
                                
                                </div>';
                break;
                case 4:
                    // =================================================================================
                    
                    if($shoppingcart->shoppingcart_payment->payment_type=="bank_transfer")
                    {
                        $amount_text = GeneralHelper::formatRupiah($shoppingcart->shoppingcart_payment->amount);
                        $account_number_text = 'Account Number';
                        
                        return '
                                <div class="card mb-1">
                                <span class="badge badge-info invoice-color-info" style="font-size:18px; ">
                                Waiting for payment <br /><b id="payment_timer" class="text-white"  style="font-size:12px; font-weight: lighter;"><i class="fa fa-spinner fa-spin fa-fw"></i></b></span>
                                </div>
                                <div class="card mb-4">
                                <input type="hidden" id="va_number" value="'. $shoppingcart->shoppingcart_payment->va_number .'">
                                <input type="hidden" id="va_total" value="'. $shoppingcart->shoppingcart_payment->amount .'">
                                <div class="card-body bg-light">

                                <div>Bank Name : </div>
                                <div class="mb-2"><b>'. Str::upper($shoppingcart->shoppingcart_payment->bank_name) .'</b></div>
                                <div>'. $account_number_text .' : </div>
                                <div class="mb-2"><b id="va">'. GeneralHelper::splitSpace($shoppingcart->shoppingcart_payment->va_number,4,0) .'</b> 
                                <button id="va_number_button" onclick="copyToClipboard(\'#va_number\')" title="Copied" data-toggle="tooltip" data-placement="right" data-trigger="click" class="btn btn-light btn-sm invoice-hilang"><i class="far fa-copy"></i></button>
                                
                                 </div>
                                <div>Total Bill : </div>
                                <div class="mb-2"><b>'. $amount_text .'</b> <button onclick="copyToClipboard(\'#va_total\')" id="va_total_button" data-toggle="tooltip" data-placement="right" title="Copied" data-trigger="click" class="btn btn-light btn-sm invoice-hilang"><i class="far fa-copy"></i></button></div>

                                
                                </div>
                                </div>
                                ';
                    }
                    // =================================================================================
                    if($shoppingcart->shoppingcart_payment->payment_type=="bank_redirect")
                    {
                        $button = '<a class="btn btn-theme w-100" href="'. $shoppingcart->shoppingcart_payment->redirect .'"><b class="invoice-hilang"> Open '.strtoupper($shoppingcart->shoppingcart_payment->bank_name).' Checkout</b></a>';
                        
                            return '
                                <div class="card mb-1">
                                <span class="badge badge-info invoice-color-info" style="font-size:18px; ">
                                Waiting for payment <br /><b id="payment_timer" class="text-white"  style="font-size:12px; font-weight: lighter;"><i class="fa fa-spinner fa-spin fa-fw"></i></b></span>
                                </div>
                                <div class="card mb-4">
                                <div class="card-body bg-light">
                                    '.$button.'
                                </div>
                                </div>
                                ';

                    }
                    // =================================================================================
                    if($shoppingcart->shoppingcart_payment->payment_type=="ewallet")
                    {
                        $button = '<a class="btn btn-theme w-100" href="'. $shoppingcart->shoppingcart_payment->redirect .'"><b class="invoice-hilang"> Open '.strtoupper($shoppingcart->shoppingcart_payment->bank_name).' App</b></a>';

                        return '
                                <div class="card mb-1">
                                <span class="badge badge-info invoice-color-info" style="font-size:18px; ">
                                Waiting for payment <br /><b id="payment_timer" class="text-white"  style="font-size:12px; font-weight: lighter;"><i class="fa fa-spinner fa-spin fa-fw"></i></b></span>
                                </div>
                                <div class="card mb-4">
                                
                                <div class="card-body bg-light">
                                    '.$button.'
                                </div>
                                </div>
                                ';
                    }
                    // =================================================================================
                    if($shoppingcart->shoppingcart_payment->payment_type=="qrcode")
                    {
                            return '
                                <div class="card mb-1">
                                <span class="badge badge-info invoice-color-info" style="font-size:18px; ">
                                Waiting for payment <br /><b id="payment_timer" class="text-white"  style="font-size:12px; font-weight: lighter;"><i class="fa fa-spinner fa-spin fa-fw"></i></b></span>
                                </div>
                                <div class="card mb-1 img-fluid invoice-hilang"  style="min-height:360px; ">
                                
                                <div class="card-img-overlay">
                                    <div class="row h-100">
                                        <div class="col-12 text-center">

                                            <img class="img-fluid border border-white mt-2" alt="QRIS LOGO" style="max-width:250px; height:30px; image-rendering: -webkit-optimize-contrast;" src="'.$asset.'/img/payment/qris-logo.png">
                                            <br />
                                            <img class="img-fluid border border-white mt-2" alt="QRIS" style="max-width:250px; image-rendering: -webkit-optimize-contrast;" src="'. BookingHelper::generate_qrcode($shoppingcart) .' ">
                                            <br />
                                            
                                        </div>
                                    </div>
                                </div>
                                
                                </div>
                                <div class="card mb-4">
                                <a href="'. env("APP_API_URL") .'/qrcode/'.$shoppingcart->session_id.'/'. $shoppingcart->confirmation_code .'" type="button" class="invoice-hilang btn btn-success invoice-hilang ">or Download QRCODE <i class="fas fa-download"></i> </a>
                                </div>
                                ';
                    }
                break;
                default:
                    return '';
            }


            
        }
        return '';
    }

    public static function create_refund($shoppingcart)
    {
        
        if($shoppingcart->shoppingcart_payment->payment_provider=="xendit")
        {
            $payment = new XenditHelper;
            $payment->createRefund($shoppingcart);
        }

        if($shoppingcart->shoppingcart_payment->payment_provider=="stripe")
        {
            $payment = new StripeHelper;
            $payment->createRefund($shoppingcart);
        }

        if($shoppingcart->shoppingcart_payment->payment_provider=="paypal")
        {
            $payment = new PaypalHelper;
            $payment->createRefund($shoppingcart);
        }
    }

    public static function create_payment($sessionId,$payment_provider="none",$bank="",$param1="")
    {
        $shoppingcart = Cache::get('_'. $sessionId);

        $first_name = BookingHelper::get_answer($shoppingcart,'firstName');
        $last_name = BookingHelper::get_answer($shoppingcart,'lastName');
        $email = BookingHelper::get_answer($shoppingcart,'email');
        $phone = BookingHelper::get_answer($shoppingcart,'phoneNumber');

        $contact = new \stdClass();
        $contact->first_name = $first_name;
        $contact->last_name = $last_name;
        $contact->name = $first_name .' '. $last_name;
        //$contact->email = $email;
        $contact->email = BookingHelper::set_maskingEmail($shoppingcart);
        $contact->phone = BookingHelper::set_maskingPhone($shoppingcart);
        //$contact->phone = $phone;

        $due_date = BookingHelper::due_date($shoppingcart);

        $date1 = Carbon::now();
        $date2 = Carbon::parse($due_date);
        $mins_expired  = $date2->diffInMinutes($date1, true);
        $date_expired = Carbon::parse($due_date)->formatLocalized('%Y-%m-%d %H:%M:%S');
        $date_now = Carbon::parse($date1)->formatLocalized('%Y-%m-%d %H:%M:%S +0700');

        $response = NULL;
        $payment_type = NULL;
        $bank_name = NULL;
        $bank_code = NULL;
        $va_number = NULL;
        $payment_description = NULL;
        $qrcode = NULL;
        $link = NULL;
        $authorization_id = NULL;

        //$order_id = $shoppingcart->confirmation_code;
        $order_id = Uuid::uuid4()->toString();
        $amount = $shoppingcart->due_now;
        $currency = $shoppingcart->currency;
        $rate = 1;
        $rate_from = $shoppingcart->currency;
        $rate_to = $shoppingcart->currency;
        $expiration_date = $date_expired;
        $payment_status = 0;
        if($shoppingcart->booking_status=="CONFIRMED") $payment_status = 2;
        $redirect = '/booking/receipt/'. $sessionId .'/'. $shoppingcart->confirmation_code;


        $transaction = new \stdClass();
        $transaction->id = $order_id;
        $transaction->amount = $amount;
        $transaction->currency = $currency;
        $transaction->confirmation_code = $shoppingcart->confirmation_code;
        $transaction->payment_provider = $payment_provider;
        $transaction->bank = $bank;
        $transaction->mins_expired = $mins_expired;
        $transaction->date_expired = $date_expired;
        $transaction->date_now = $date_now;
        $transaction->finish_url = $redirect;
        $transaction->finish_url_full = $shoppingcart->url . $redirect;

        //============================================
        $products = array();
        foreach($shoppingcart->products as $product)
        {
            foreach($product->product_details as $product_detail)
            {
                $products[] = [
                    'title' => $product_detail->title,
                    'price' => $product_detail->price,
                    'unit' => $product_detail->unit_price,
                    'qty' => $product_detail->qty,
                    'subtotal' => $product_detail->subtotal,
                    'discount' => $product_detail->discount,
                    'total' => $product_detail->total,
                ];
            }
        }
        $transaction->products = $products;
        //============================================


        $data = new \stdClass();
        $data->contact = $contact;
        $data->transaction = $transaction;

        if($data->transaction->mins_expired==0) {
            $data->transaction->mins_expired = 60;
            $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);
        }

        switch($payment_provider)
        {
            case "xendit":

                $payment_provider = 'xendit';
                $currency = 'IDR';

                $amount = BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,$currency);
                $rate = number_format((float)$shoppingcart->due_now / $amount, 2, '.', '');
                $rate_from = $shoppingcart->currency;
                $rate_to = $currency;
                
                $data->transaction->authorization_id = $param1;
                $data->transaction->amount = round($amount);
                $data->transaction->currency = $currency;

                if($data->transaction->bank == 'ovo')
                {
                    $payment_type = 'ewallet';
                    $bank_name = 'ovo';
                    $payment_status = 2;

                    $response = new \stdClass();
                    $response->status = new \stdClass();
                    $response->status->id = 1;
                }
                
                if($data->transaction->bank == 'dana')
                {
                    $payment_type = 'ewallet';
                    $bank_name = 'dana';
                    $payment_status = 4;
                    $response = XenditHelper::createPayment($data);
                }

                if($data->transaction->bank == 'bss')
                {
                    $payment_type = 'bank_transfer';
                    $bank_name = 'Bank Sahabat Sampoerna';
                    $bank_code = '523';
                    $payment_status = 4;
                    $response = XenditHelper::createPayment($data);
                }

                if($data->transaction->bank == 'qris')
                {
                    $payment_type = 'qrcode';
                    $bank_name = 'qris';
                    $payment_status = 4;
                    $response = XenditHelper::createPayment($data);
                }

                if($data->transaction->bank == 'invoice')
                {
                    $payment_type = 'bank_redirect';
                    $payment_status = 4;
                    $response = XenditHelper::createPayment($data);
                }

                if($data->transaction->bank == 'card')
                {
                    $payment_type = 'card';
                    $payment_status = 0;
                    $response = XenditHelper::createPayment($data);
                }



            break;
            case "paypal":
                $payment_provider = 'paypal';
                $currency = env("PAYPAL_CURRENCY");

                $amount = BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,$currency);
                $rate = number_format((float)$shoppingcart->due_now / $amount, 2, '.', '');
                $rate_from = $shoppingcart->currency;
                $rate_to = $currency;

                $data->transaction->amount = $amount;
                $data->transaction->currency = $currency;

                $payment_status = 0;

                $response = PaypalHelper::createPayment($data);

            break;
            case "stripe":
                $payment_provider = 'stripe';
                $currency = 'USD';

                $amount = BookingHelper::convert_currency($shoppingcart->due_now,$shoppingcart->currency,$currency);
                $rate = number_format((float)$shoppingcart->due_now / $amount, 2, '.', '');
                $rate_from = $shoppingcart->currency;
                $rate_to = $currency;

                $data->transaction->amount = $amount;
                $data->transaction->currency = $currency;

                $payment_status = 0;

                $response = StripeHelper::createPayment($data);

            break;
            default:
                $response = new \stdClass();
                $status_json = new \stdClass();

                $status_json->id = 1;
                $status_json->message = 'success';

                $response->status = $status_json;
                $response->data = null;
        }

        if($response->status->id=="0")
        {
            unset($response->data);
            return $response;
        }
        
        if(isset($response->data->payment_type)) $payment_type = $response->data->payment_type;
        if(isset($response->data->currency)) $currency = $response->data->currency;
        if(isset($response->data->rate)) $rate = $response->data->rate;
        if(isset($response->data->rate_from)) $rate_from = $response->data->rate_from;
        if(isset($response->data->rate_to)) $rate_to = $response->data->rate_to;
        if(isset($response->data->bank_name)) $bank_name = $response->data->bank_name;
        if(isset($response->data->bank_code)) $bank_code = $response->data->bank_code;
        if(isset($response->data->va_number)) $va_number = $response->data->va_number;
        if(isset($response->data->qrcode)) $qrcode = $response->data->qrcode;
        if(isset($response->data->link)) $link = $response->data->link;
        if(isset($response->data->redirect)) $redirect = $response->data->redirect;
        if(isset($response->data->expiration_date)) $expiration_date = $response->data->expiration_date;
        if(isset($response->data->order_id)) $order_id = $response->data->order_id;
        if(isset($response->data->authorization_id)) $authorization_id = $response->data->authorization_id;
        if(isset($response->data->amount)) $amount = $response->data->amount;
        if(isset($response->data->payment_description)) $payment_description = $response->data->payment_description;
        if(isset($response->data->payment_status)) $payment_status = $response->data->payment_status;

        $ShoppingcartPayment = (object) array(
            'payment_provider' => $payment_provider,
            'payment_type' => $payment_type,
            'bank_name' => $bank_name,
            'bank_code' => $bank_code,
            'va_number' => $va_number,
            'qrcode' => $qrcode,
            'link' => $link,
            'redirect' => $redirect,
            'order_id' => $order_id,
            'authorization_id' => $authorization_id,
            'amount' => $amount,
            'currency' => $currency,
            'rate' => $rate,
            'rate_from' => $rate_from,
            'rate_to' => $rate_to,
            'expiration_date' => $expiration_date,
            'payment_description' => $payment_description,
            'payment_status' => $payment_status,
        );

        $shoppingcart->payment = $ShoppingcartPayment;
        Cache::forget('_'. $sessionId);
        Cache::add('_'. $sessionId, $shoppingcart, 172800);

        return $response;
    }





}
?>
