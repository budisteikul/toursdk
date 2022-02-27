<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;

use budisteikul\toursdk\Helpers\OyHelper;

use Illuminate\Http\Request;

class DisbursementController extends Controller
{
	public function disbursement_connect(Request $request)
    {
    	$response = NULL;
    	$data = $request->getContent();
    	$data = json_decode($data);

    	return response()->json([
            "response" => $response
        ]);
    }

    

    
}