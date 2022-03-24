<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;
use budisteikul\toursdk\Helpers\FirebaseHelper;
use Carbon\Carbon;
use budisteikul\toursdk\Helpers\BookingHelper;

class DirectTransferHelper {

	public static function env_appName()
  	{
        return env("APP_NAME");
  	}

    public static function env_bokunCurrency()
    {
        return env("BOKUN_CURRENCY");
    }
	
    public static function bankCode($bank)
    {
        $data = new \stdClass();
        switch($bank)
        {
            case "usd_wise":
                $data->bank_name = "Wise";
                $data->bank_payment_type = "usd_wise";
                $data->bank_currency = "USD";
            break;
            case "eur_wise":
                $data->bank_name = "Wise";
                $data->bank_payment_type = "eur_wise";
                $data->bank_currency = "EUR";
            break;
            case "aud_wise":
                $data->bank_name = "Wise";
                $data->bank_payment_type = "aud_wise";
                $data->bank_currency = "AUD";
            break;
            case "sgd_wise":
                $data->bank_name = "Wise";
                $data->bank_payment_type = "sgd_wise";
                $data->bank_currency = "SGD";
            break;
            case "gbp_wise":
                $data->bank_name = "Wise";
                $data->bank_payment_type = "gbp_wise";
                $data->bank_currency = "GBP";
            break;
            default:
                return response()->json([
                    "message" => 'Error'
                ]);   
        }

        return $data;
    }

    public static function createPayment($data)
    {
        $payment = self::bankCode($data->transaction->bank);

        $response = new \stdClass();

        if($payment->bank_payment_type=="usd_wise")
        {
            $response->payment_type = 'direct_transfer';
            $response->account_holder = 'VERTIKAL TRIP LLC';
            $response->swift_code = '084009519';
            $response->account_number = '96 0000 0000 513929';
            $response->bank_address = '19 W 24th Street <br />New York NY 10010 <br />United States';
        }

        if($payment->bank_payment_type=="eur_wise")
        {
            $response->payment_type = 'direct_transfer';
            $response->account_holder = 'VERTIKAL TRIP LLC';
            $response->swift_code = 'TRWIBEB1XXX';
            $response->iban_code = 'BE17 9670 4157 0021';
            $response->bank_address = 'Avenue Louise 54, Room S52 <br />Brussels 1050 Belgium';
        }

        if($payment->bank_payment_type=="aud_wise")
        {
            $response->payment_type = 'direct_transfer';
            $response->account_holder = 'VERTIKAL TRIP LLC';
            $response->bank_code = '802-985';
            $response->account_number = '611296712';
            $response->bank_address = '36-38 Gipps Street <br />Collingwood 3066 Australia';
        }

        if($payment->bank_payment_type=="sgd_wise")
        {
            $response->payment_type = 'direct_transfer';
            $response->bank_name = 'Wise Asia-Pasific Pte. Ltd.';
            $response->account_holder = 'VERTIKAL TRIP LLC';
            $response->bank_code = '0516';
            $response->account_number = '193-236-7';
            $response->bank_address = '1 Paya Lebar Link #13-06 - #13-08 <br />PLQ 2, Paya Lebar Quarter <br />Singapore 408533';
        }

        if($payment->bank_payment_type=="gbp_wise")
        {
            $response->payment_type = 'direct_transfer';
            $response->account_holder = 'VERTIKAL TRIP LLC';
            $response->bank_code = '23-14-70';
            $response->account_number = '59769383';
            $response->iban_code = 'GB37 TRWI 2314 7059 7693 83';
            $response->bank_address = '56 Shoreditch High Street <br />London E1 6JJ <br />United Kingdom';
        }

        $response->amount = BookingHelper::convert_currency($data->transaction->amount,self::env_bokunCurrency(),$payment->bank_currency);
        $response->currency = self::env_bokunCurrency();
        $response->rate = BookingHelper::convert_currency(1,self::env_bokunCurrency(),$payment->bank_currency);
        $response->rate_from = self::env_bokunCurrency();
        $response->rate_to = $payment->bank_currency;

        
        $response->redirect = $data->transaction->finish_url;
        $response->expiration_date = $data->transaction->date_expired;
        $response->order_id = $data->transaction->id;
        
        return $response;
    }

    

}