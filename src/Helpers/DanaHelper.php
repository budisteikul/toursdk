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
        $data = danaCreateOrder($data);
        $redirect_url = $data['response']['body']['checkoutUrl'];

        $response->bank_name = 'dana';
        $response->link = null;
        $response->expiration_date = $data->transaction->date_expired;
        $response->order_id = $data->transaction->id;
        $response->payment_type = 'ewallet';
        $response->redirect = $redirect_url;
        return $response;
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
          //'accessToken'  => $accessToken ? $accessToken : '',
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
                'expiryTime'        => date('Y-m-d\TH:i:sP', strtotime('now +1 hour')),
                'orderTitle'        => 'Payment for '. $data->transaction->confirmation_code,
                'merchantTransId'   => $data->transaction->confirmation_code,
                'orderMemo'         => '',
                'createdTime'       => date('Y-m-d\TH:i:sP'),
                'orderAmount'       => [
                    'value'    => $data->transaction->amount, //aaaaaaa
                    'currency' => 'IDR'
                ],
            ],
            'productCode'      => '51051000100000000001',
            'mcc'              => '123',
            'merchantId'       => self::env_danaMerchantId(),
            'extendInfo'       => '',
            'notificationUrls' => [
                    [
                        'type' => 'PAY_RETURN',
                        'url'  => self::env_appUrl() . $data->transaction->finish_url
                    ],
                    [
                        'type' => 'NOTIFICATION',
                        'url'  => self::env_appApiUrl() .'/payment/dana/confirm'
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