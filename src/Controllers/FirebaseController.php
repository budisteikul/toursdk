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
    
    public function billing($sessionId,Request $request)
    {
        $postal_code = $request->input('postal_code');
        $data = [
            'postal_code' => $postal_code
        ];
        FirebaseHelper::write('billing/'.$sessionId,$data);
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

}
