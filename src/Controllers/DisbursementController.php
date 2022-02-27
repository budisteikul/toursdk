<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Helpers\OyHelper;

use Illuminate\Http\Request;

class DisbursementController extends Controller
{

    public function __construct()
    {
        $this->midtransServerKey = env("MIDTRANS_SERVER_KEY",NULL);
        $this->oyApiKey = env("OY_API_KEY",NULL);
        $this->dokuSecretKey = env("DOKU_SECRET_KEY",NULL);
    }

	public function disbursement_connect(Request $request)
    {
    	$response = NULL;
    	$data = $request->getContent();
    	$data = json_decode($data);

        if($this->oyApiKey==$data->api_key)
        {
            $response = OyHelper::createDisbursement($data->disbursement);
        }
        

    	return response()->json([
            "response" => $response
        ]);
    }

    

    
}