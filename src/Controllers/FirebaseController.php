<?php
namespace budisteikul\toursdk\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\LogHelper;
use budisteikul\toursdk\Helpers\FirebaseHelper;

class FirebaseController extends Controller
{
    
	
    public function __construct()
    {
        
    }
    
    public function test()
    {
        $aaa = FirebaseHelper::read('billing/d777288-cff7-f28-567c-85c68d078');
        print_r($aaa->surname);
    }

    public function billing($sessionId,Request $request)
    {
        $given_name = $request->input('givenName');
        $surname = $request->input('surname');
        $street_line1 = $request->input('streetLine1');
        $postal_code = $request->input('postalCode');
        $token_id = $request->input('tokenId');
        $country = $request->input('country');
        
        $data = [
            'given_name' => $given_name,
            'surname' => $surname,
            'street_line1' => $street_line1,
            'postal_code' => $postal_code,
            'country' => $country,
            'token_id' => $token_id,
        ];
        
        FirebaseHelper::write('billing/'.$sessionId,$data);
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

}
