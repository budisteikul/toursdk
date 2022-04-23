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
  		$amount = bcmul($amount, 100);

  		Stripe\Stripe::setApiKey(self::env_stripeSecretKey());
  		$intent = Stripe\PaymentIntent::create([
  			'amount' => $amount,
  			'currency' => 'usd',
  			'metadata' => ['integration_check' => 'accept_a_payment'],
  			//'capture_method' => 'manual',
		]);

  		$response = new \stdClass();
  		$response->intent = $intent;
  		$response->authorization_id = $intent->id;
		return $response;
  	}
}