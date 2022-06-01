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
        $response = new \stdClass();

        $data->transaction->mins_expired = 60;
        $data->transaction->date_expired = Carbon::parse($data->transaction->date_now)->addMinutes($data->transaction->mins_expired);
        $data->transaction->dana_created_time = date('Y-m-d\TH:i:sP');
        $data->transaction->dana_expired_time = date('Y-m-d\TH:i:sP', strtotime('+1 hour'));

        $data1 = self::danaCreateOrder($data);

        $redirect_url = $data1['response']['body']['checkoutUrl'];
        $acquirementId = $data1['response']['body']['acquirementId'];
       
        $data2 = self::danaCreateSPI($data,$acquirementId);

        //print_r($data1);
        //print_r($data2);

        $response->authorization_id = $acquirementId;
        $response->bank_name = 'dana';
        $response->link = null;
        $response->expiration_date = $data->transaction->date_expired;
        $response->order_id = $data->transaction->id;
        $response->payment_type = 'ewallet';
        $response->redirect = $redirect_url;
        return $response;
    }

    public static function danaCreateSPI($data,$acquirementId)
    {
        $merchantTransId = $data->transaction->id; 
        $acquirementId  = $acquirementId; 
        $acquirementStatus = 'CLOSED || SUCCESS';
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
                'orderAmount'   => $orderAmount,
                'createdTime'   => $createdTime,
                'finishedTime'   => $finishedTime,
            ]
        ];

        $data_json = self::composeRequest($requestData);
        
        $endpoint = self::danaApiEndpoint() ."/dana/acquiring/order/finishNotify.htm";

        $headers = [
              'Content-Type' => 'application/json',
              'Cache-control' => 'no-cache',
              'X-DANA-SDK' => 'PHP',
              'X-DANA-SDK-VERSION' => '1.0'
          ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data_json]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data,true);

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
                ],
                'productCode'      => '51051000100000000001',
                'mcc'              => '123',
                'merchantId'       => self::env_danaMerchantId(),
                'extendInfo'       => '',
                'paymentPreference' => [
                    'disabledPayMethods' => 'OTC || CREDIT_CARD || VIRTUAL_ACCOUNT || DEBIT_CARD || DIRECT_DEBIT_CREDIT_CARD || DIRECT_DEBIT_DEBIT_CARD'
                ],
                'notificationUrls' => [
                    [
                        'type' => 'PAY_RETURN',
                        //'url'  => self::env_appUrl() . $data->transaction->finish_url
                        'url'  => 'https://sandbox.vertikaltrip.com/cms/booking'
                    ],
                    [
                        'type' => 'NOTIFICATION',
                        //'url'  => self::env_appApiUrl() .'/payment/dana/confirm'
                        'url'  => 'https://webhook.site/#!/cb949324-c2af-4c11-8341-dcfae6f52221'
                    ],
                ]
            ]

        ];

        $data_json = self::composeRequest($requestData);
        
        $endpoint = self::danaApiEndpoint() ."/dana/acquiring/order/createOrder.htm";

        $headers = [
              'Content-Type' => 'application/json',
              'Cache-control' => 'no-cache',
              'X-DANA-SDK' => 'PHP',
              'X-DANA-SDK-VERSION' => '1.0'
          ];

        $client = new \GuzzleHttp\Client(['headers' => $headers,'http_errors' => false]);
        $response = $client->request('POST',$endpoint,
          ['json' => $data_json]
        );

        $data = $response->getBody()->getContents();
        $data = json_decode($data,true);

        return $data;
        
    }

    public static function generateSignature($data, $privateKey)
    {
      
      $signature = '';
      openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

      return base64_encode($signature);
    }

    public static function composeRequest($requestData)
    {
      

      $requestDataText = json_encode($requestData, JSON_UNESCAPED_SLASHES);
      $requestDataText = preg_replace('/\\\\\\\"/',"\"", $requestDataText); // remove unnecessary double escape
      $signature       = self::generateSignature($requestDataText, self::env_danaPrivateKey());

      $requestPayload = [
          'request'   => $requestData,
          'signature' => $signature
      ];

      //$requestPayloadText = json_encode($requestPayload, JSON_UNESCAPED_SLASHES);
      //$requestPayloadText = preg_replace('/\\\\\\\"/',"\"", $requestPayloadText); // remove unnecessary double escape

      return $requestPayload;
    }

    

}