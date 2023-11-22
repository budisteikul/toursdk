<?php
namespace budisteikul\toursdk\Helpers;

use Stripe;

class StripeHelper {

	public static function env_stripePublishableKey()
  	{
        return env("STRIPE_PUBLISHABLE_KEY");
  	}

  	public static function env_stripeSecretKey()
  	{
        return env("STRIPE_SECRET_KEY");
  	}

  	public static function createPayment($data)
  	{
  		$amount = number_format((float)$data->transaction->amount, 2, '.', '');
  		//$amount = bcmul($amount, 100);
  		$amount = $amount * 100;

  		Stripe\Stripe::setApiKey(self::env_stripeSecretKey());
  		$intent = Stripe\PaymentIntent::create([
  			'amount' => $amount,
  			'currency' => 'usd',
  			'metadata' => ['integration_check' => 'accept_a_payment'],
  			//'capture_method' => 'manual',
		]);

        LogHelper::log($intent,'stripe');

        $data_json = new \stdClass();
        $status_json = new \stdClass();
        $response_json = new \stdClass();
      
  		$data_json->intent = $intent;
  		$data_json->authorization_id = $intent->id;

        $status_json->id = '1';
        $status_json->message = 'success';
        
        $response_json->status = $status_json;
        $response_json->data = $data_json;

		return $response_json;
  	}

    public function createRefund($id,$amount)
    {
        Stripe\Stripe::setApiKey(self::env_stripeSecretKey());
        $refund = Stripe\Refund::create([
            'amount' => $amount,
            'payment_intent' => $id
        ]);
        return $refund;
    }
}