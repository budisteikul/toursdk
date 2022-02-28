<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Helpers\MidtransHelper;
use budisteikul\toursdk\Helpers\OyHelper;
use budisteikul\toursdk\Helpers\DokuHelper;

use Illuminate\Http\Request;
use Cache;

class PaymentController extends Controller
{

    public function __construct()
    {
        $this->midtransServerKey = env("MIDTRANS_SERVER_KEY",NULL);
        $this->oyApiKey = env("OY_API_KEY",NULL);
        $this->dokuSecretKey = env("DOKU_SECRET_KEY",NULL);
    }

	public function payment_connect(Request $request)
    {
    	$response = NULL;
    	$data = $request->getContent();
        $data = json_decode($data);
    	
        switch($data->transaction->payment_provider)
        {
        	case "oyindonesia":
                if($this->oyApiKey==$data->transaction->api_key)
                {
                    $response = OyHelper::createPayment($data);
                }
        	break;
        	case "midtrans":
                if($this->midtransServerKey==$data->transaction->api_key)
                {
        		  $response = MidtransHelper::createPayment($data);
                }
        	break;
        	case "doku":
                if($this->dokuSecretKey==$data->transaction->api_key)
                {
        		  $response = DokuHelper::createPayment($data);
                }
        	break;
        	default:
        		return "";
        }

    	return response()->json([
            "response" => $response
        ]);
    }

    

    
}