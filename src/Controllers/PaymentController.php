<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Helpers\MidtransHelper;
use budisteikul\toursdk\Helpers\OyHelper;
use budisteikul\toursdk\Helpers\DokuHelper;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
	public function payment_connect(Request $request)
    {
    	$response = NULL;
    	$data = $request->getContent();
    	$data = json_decode($data);
    	
        switch($data->transaction->payment_provider)
        {
        	case "oyindonesia":
        		$response = OyHelper::createPayment($data);
        	break;
        	case "midtrans":
        		$response = MidtransHelper::createPayment($data);
        	break;
        	case "doku":
        		$response = DokuHelper::createPayment($data);
        	break;
        	default:
        		return "";
        }

    	return response()->json([
            "response" => $response
        ]);
    }

    

    
}