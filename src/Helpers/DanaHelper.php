<?php
namespace budisteikul\toursdk\Helpers;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class DanaHelper {

    public static function env_appUrl()
    {
        return env("APP_URL");
    }

    public static function env_appName()
    {
        return env("APP_NAME");
    }

    public static function env_appApiUrl()
    {
        return env("APP_API_URL");
    }

    public static function env_danaEnv()
    {
        return env("DANA_ENV");
    }

    public static function env_danaMerchantId()
    {
        return env("DANA_MERCHANT_ID");
    }

    public static function env_danaClientId()
    {
        return env("DANA_CLIENT_ID");
    }

    public static function env_danaClientSecret()
    {
        return env("DANA_CLIENT_SECRET");
    }

    public static function env_danaPrivateKey()
    {
        return env("DANA_PRIVATE_KEY");
    }

    public static function env_danaPublicKey()
    {
        return env("DANA_PUBLIC_KEY");
    }

    public static function danaApiEndpoint()
    {
        if(self::env_danaEnv()=="production")
        {
            $endpoint = "https://api.saas.dana.id";
        }
        else
        {
            $endpoint = "https://api.sandbox.dana.id";
        }
        return $endpoint;
    }


    public static function createPayment($data)
    {
        $status_json = new \stdClass();
        $data_json = new \stdClass();
        $response_json = new \stdClass();

        $data->transaction->mins_expired = 60;
        $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);
        $data->transaction->dana_created_time = date('Y-m-d\TH:i:sP');
        $data->transaction->dana_expired_time = date('Y-m-d\TH:i:sP', strtotime('+1 hour'));

        $data1 = self::danaCreateOrder($data);

        $data1 = json_decode($data1, true);

        //print_r($data1);
        //exit();
        
        if($data1['response']['body']['resultInfo']['resultCode']=="SYSTEM_ERROR")
	    {
              $status_json->id = '0';
              $status_json->message = 'Failed to create transaction, please try again';

              $response_json->status = $status_json;

		      return $response_json;
	    }

        if($data1['response']['body']['resultInfo']['resultCode']=="AMOUNT_EXCEEDS_LIMIT")
        {
              $status_json->id = '0';
              $status_json->message = 'Order amount exceeds limit, try another payment method';

              $response_json->status = $status_json;

              return $response_json;
        }
	    
        $redirect_url = $data1['response']['body']['checkoutUrl'];
        $acquirementId = $data1['response']['body']['acquirementId'];
       
        $data2 = self::danaCreateSPI($data,$acquirementId);
        
        
        $status_json->id = '1';
        $status_json->message = 'success';
        
        $data_json->authorization_id = $acquirementId;
        $data_json->bank_name = 'dana';
        $data_json->link = null;
        $data_json->expiration_date = $data->transaction->date_expired;
        $data_json->order_id = $data->transaction->id;
        $data_json->payment_type = 'ewallet';
        $data_json->redirect = $redirect_url;

        $response_json->status = $status_json;
        $response_json->data = $data_json;

        return $response_json;
    }

    public static function danaCreateSPI($data,$acquirementId)
    {
        $merchantTransId = $data->transaction->id; 
        $acquirementId  = $acquirementId; 
        $acquirementStatus = 'SUCCESS';
        $orderAmount = $data->transaction->amount * 100;
        $createdTime = $data->transaction->dana_created_time;
        $finishedTime = $data->transaction->dana_expired_time;

        $requestData = [
            'head' => [
                'version'      => '2.0',
                'function'     => 'dana.acquiring.order.finishNotify',
                'clientId'     => self::env_danaClientId(),
                'reqTime'      => date('Y-m-d\TH:i:sP'),
                'reqMsgId'     => Uuid::uuid4()->toString(),
            ],
            'body' => [
                'merchantId'      => self::env_danaMerchantId(),
                'merchantTransId' => $merchantTransId,
                'acquirementId'   => $acquirementId,
                'acquirementStatus'   => $acquirementStatus,
                'orderAmount'   => [
                    'currency' => 'IDR',
                    'value' => $orderAmount
                ],
                'createdTime'   => $createdTime,
                'finishedTime'   => $finishedTime,
            ]
        ];

        $endpoint = self::danaApiEndpoint() ."/dana/acquiring/order/finishNotify.htm";

        $data = self::danaApi($endpoint,$requestData);

        return $data;
    }

    

    public static function danaCreateOrder($data)
    {

        $requestData = [
            'head' => [
                'version'      => '2.0',
                'function'     => 'dana.acquiring.order.createOrder',
                'clientId'     => self::env_danaClientId(),
                'clientSecret' => self::env_danaClientSecret(),
                'reqTime'      => date('Y-m-d\TH:i:sP'),
                'reqMsgId'     => Uuid::uuid4()->toString(),
                'reserve'      => '{}',
            ],
            'body' => [
                'envInfo'          => [
                    'terminalType'       => 'SYSTEM',
                    'osType'             => '',
                    'extendInfo'         => '',
                    'orderOsType'        => '',
                    'sdkVersion'         => '',
                    'websiteLanguage'    => '',
                    'tokenId'            => '',
                    'sessionId'          => '',
                    'appVersion'         => '',
                    'merchantAppVersion' => '',
                    'clientKey'          => '',
                    'orderTerminalType'  => 'SYSTEM',
                    'clientIp'           => '',
                    'sourcePlatform'     => 'IPG'
                ],
                'order'            => [
                    'expiryTime'        => $data->transaction->dana_expired_time,
                    'orderTitle'        => 'Payment for order ID '. $data->transaction->confirmation_code,
                    'merchantTransId'   => $data->transaction->confirmation_code,
                    'orderMemo'         => '',
                    'createdTime'       => $data->transaction->dana_created_time,
                    'orderAmount'       => [
                        'value'    => $data->transaction->amount * 100,
                        'currency' => 'IDR'
                    ],
		            'goods' => [
                        [
                            'merchantGoodsId' => $data->transaction->confirmation_code,
                            'description' => 'Payment for order ID '. $data->transaction->confirmation_code,
                            'category' => self::env_appName(),
                            'price' => [
                                'currency' => 'IDR',
                                'value' => $data->transaction->amount * 100
                            ],
                            'unit' => null,
                            'quantity' => null,
                            'merchantShippingId' => null,
                            'snapshotUrl' => null,
                            'extendInfo' => []
                        ]
                    ],
                ],
                
                'productCode'      => '51051000100000000001',
                'mcc'              => '4722',
                'merchantId'       => self::env_danaMerchantId(),
                'extendInfo'       => '',
                'paymentPreference' => [
                    'disabledPayMethods' => 'OTC^DEBIT_CARD'
                ],
                'notificationUrls' => [
                    [
                        'url'  => self::env_appUrl() . $data->transaction->finish_url,
                        'type' => 'PAY_RETURN'
                    ]
                    ,
                    [
                        'url'  => self::env_appApiUrl() .'/payment/dana/confirm',
                        'type' => 'NOTIFICATION'
                        //'url'  => 'https://webhook.site/35cabc97-b13c-4778-ade4-c7b192762c1b'
                    ],
                ]
            ]

        ];
        
        $endpoint = self::danaApiEndpoint() ."/dana/acquiring/order/createOrder.htm";

        $data = self::danaApi($endpoint,$requestData);

        return $data;
        
    }


    public static function composeResponse($data)
    {
      
      $responseData = [
            'head' => [
                'version'      => '2.0',
                'function'     => 'dana.acquiring.order.finishNotify',
                'clientId'     => self::env_danaClientId(),
                'clientSecret' => self::env_danaClientSecret(),
                'respTime'      => date('Y-m-d\TH:i:sP'),
                'reqMsgId'     => $data['request']['head']['reqMsgId']
            ],
            'body' => [
		    'resultInfo' => [
		    	'resultStatus' => 'S',
			'resultCodeId' => '00000000',
			'resultCode' => 'SUCCESS',
			'resultMsg' => 'success'
		    ]
            ]
        ]; 
      
      array_walk_recursive($responseData, function (&$item) {
          $item = strval($item);
      });

      $responseDataText = json_encode($responseData, JSON_UNESCAPED_SLASHES);
      $signature        = self::generateSignature($responseDataText, self::env_danaPrivateKey());
         
      $responsePayload = [
          'response'  => $responseData,
          'signature' => $signature
      ];
     
      return json_encode($responsePayload, JSON_UNESCAPED_SLASHES);
    }

    

    public static function generateSignature($data, $privateKey)
    {
      
      $signature = '';
      openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

      return base64_encode($signature);
    }

    public static function composeRequest($requestData)
    {
      
      array_walk_recursive($requestData, function (&$item) {
          $item = strval($item);
      });
	    
      $requestDataText = json_encode($requestData, JSON_UNESCAPED_SLASHES);
      $requestDataText = preg_replace('/\\\\\\\"/',"\"", $requestDataText); // remove unnecessary double escape
      $signature       = self::generateSignature($requestDataText, self::env_danaPrivateKey());

      $requestPayload = [
          'request'   => $requestData,
          'signature' => $signature
      ];

      $requestPayloadText = json_encode($requestPayload, JSON_UNESCAPED_SLASHES);
      $requestPayloadText = preg_replace('/\\\\\\\"/',"\"", $requestPayloadText); // remove unnecessary double escape

      return $requestPayloadText;
    }

    public static function checkSignature($data)
    {
        $payloadText = json_encode($data);
        $firstOffset = strpos($payloadText, '{"head"');
        $lastOffset  = strpos($payloadText, ',"signature"');
        $body        = substr($payloadText, $firstOffset, $lastOffset - $firstOffset);

        $payloadObject   = json_decode($payloadText, true);
        $signatureBase64 = $payloadObject['signature'];

        $binarySignature = base64_decode($signatureBase64);
        $publicKey = self::env_danaPublicKey();
        //$publicKey = self::env_danaPrivateKey();

        return (bool)openssl_verify($body, $binarySignature, $publicKey, OPENSSL_ALGO_SHA256);
    }

    public static function danaApi($url, $payloadObject)
    {
      //$isMockApi = Config::$isMockApi;
      //$isMockScene = Config::$isMockScene;
      
      $jsonPayload = self::composeRequest($payloadObject);


      //print_r($jsonPayload);

      $curl = curl_init();
      $opts = [
          CURLOPT_URL            => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING       => "",
          CURLOPT_MAXREDIRS      => 10,
          CURLOPT_TIMEOUT        => 30,
          CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST  => "POST",
          CURLOPT_POSTFIELDS     => $jsonPayload,
          CURLOPT_HTTPHEADER     => [
              "Content-Type: application/json",
              "Cache-control: no-cache",
              "X-DANA-SDK: PHP",
              "X-DANA-SDK-VERSION: 1.0",
          ]
      ];

      curl_setopt_array($curl, $opts);

      $response = curl_exec($curl);
      $err      = curl_error($curl);
      
      //print_r($response);
      //print_r($err);

      curl_close($curl);

      return $response;
    }

}
