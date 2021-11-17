<?php
namespace budisteikul\toursdk\Helpers;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class PaypalHelper {
	
	public static function client()
    {
        return new PayPalHttpClient(self::environment());
    }
	
	public static function environment()
    {
        $clientId = env("PAYPAL_CLIENT_ID");
        $clientSecret = env("PAYPAL_CLIENT_SECRET");
		if(env("PAYPAL_ENV")=="production")
			{
        		return new ProductionEnvironment($clientId, $clientSecret);
			}
			else
			{
				return new SandboxEnvironment($clientId, $clientSecret);
			}
    }
	
	public static function getOrder($id)
    {
		  $client = self::client();
		  $response = $client->execute(new OrdersGetRequest($id));
		  /*
		  print "Status Code: {$response->statusCode}\n";
    	print "Status: {$response->result->status}\n";
    	print "Order ID: {$response->result->id}\n";
    	print "Intent: {$response->result->intent}\n";
    	print "Links:\n";
    	foreach($response->result->links as $link)
    	{
      		print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    	}
    	print "Gross Amount: {$response->result->purchase_units[0]->amount->currency_code} {$response->result->purchase_units[0]->amount->value}\n";
		  */
		  return $response->result->purchase_units[0]->amount->value;
	}
	
	public static function createOrder($shoppingcart)
  	{

      $value = number_format((float)$shoppingcart->payment->amount, 2, '.', '');
      $name = $shoppingcart->confirmation_code;
      $currency = $shoppingcart->payment->currency;
    	
      $request = new OrdersCreateRequest();
    	$request->prefer('return=representation');
    	$request->body = self::buildRequestBodyCreateOrder($value,$name,$currency);
    	$client = self::client();
    	$response = $client->execute($request);
    	return $response;
  	}
	
	
	public static function buildRequestBodyCreateOrder($value,$name,$currency)
    {
        return array(
            'intent' => 'AUTHORIZE',
            'application_context' =>
                array(
                    'shipping_preference' => 'NO_SHIPPING'
                ),
            'purchase_units' =>
                array(
                    0 =>
                        array(
						'description' => $name,
                            'amount' =>
                                array(
                                    'currency_code' => $currency,
                                    'value' => $value
                                )
                        )
                )
        );
    }
	
	public static function captureAuth($id)
    {
		$request = new AuthorizationsCaptureRequest($id);
    	$request->body = self::buildRequestBodyCapture();
    	$client = self::client();
    	$response = $client->execute($request);
    	/*
		if ($debug)
    	{
      		print "Status Code: {$response->statusCode}\n";
      		print "Status: {$response->result->status}\n";
      		print "Capture ID: {$response->result->id}\n";
      		print "Links:\n";
      		foreach($response->result->links as $link)
      		{
        		print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
      		}
      		// To toggle printing the whole response body comment/uncomment
      		// the follwowing line
      		echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
    	}
   	  */
	  return $response->result->status;
	}
	
	public static function buildRequestBodyCapture()
  	{
    		return "{}";
  	}
	
	public static function voidPaypal($id)
  	{
			$PAYPAL_CLIENT = env("PAYPAL_CLIENT_ID");
			$PAYPAL_SECRET = env("PAYPAL_CLIENT_SECRET");

			// 1b. Point your server to the PayPal API
			if(env("PAYPAL_ENV")=="production")
			{
				$PAYPAL_OAUTH_API         = 'https://api.paypal.com/v1/oauth2/token/';
				$PAYPAL_AUTHORIZATION_API = 'https://api.paypal.com/v2/payments/authorizations/';
			}
			else
			{
				$PAYPAL_OAUTH_API         = 'https://api.sandbox.paypal.com/v1/oauth2/token/';
				$PAYPAL_AUTHORIZATION_API = 'https://api.sandbox.paypal.com/v2/payments/authorizations/';
			}
			
			$basicAuth = base64_encode($PAYPAL_CLIENT.':'.$PAYPAL_SECRET);
    	$headers = [
          		'Accept' => 'application/json',
          		'Authorization' => 'Basic '.$basicAuth,
        		];
			$client = new \GuzzleHttp\Client(['headers' => $headers]);
    	$response = $client->request('POST', $PAYPAL_OAUTH_API,[
			'form_params' => [
        		'grant_type' => 'client_credentials',
    		]
			]);
			
			$data = json_decode($response->getBody(), true);
			$access_token = $data['access_token'];
			
			$headers = [
          		'Accept' => 'application/json',
          		'Authorization' => 'Bearer '.$access_token,
        		];
			$client = new \GuzzleHttp\Client(['headers' => $headers]);
    	$response = $client->request('POST', $PAYPAL_AUTHORIZATION_API . $id.'/void');
			
  	}
}
?>