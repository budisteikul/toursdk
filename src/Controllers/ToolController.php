<?php
namespace budisteikul\toursdk\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\LogHelper;
use budisteikul\toursdk\Helpers\FirebaseHelper;
use Illuminate\Support\Facades\Cache;

class ToolController extends Controller
{
    
	
    public function __construct()
    {
        
    }

    public function bin(Request $request)
    {
        $bin = $request->input("bin");
        if(!is_numeric($bin))
        {
            return "";
        }
        if(strlen($bin)!=8)
        {
            return "";
        }

        $value = Cache::rememberForever('_bin_'. $bin, function ()  use ($bin){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_URL, env("MIDTRANS_URL")."/v1/bins/".$bin);

            $headerArray[] = "Accept: application/json";
            $headerArray[] = "Authorization: Basic ". base64_encode(env("MIDTRANS_SERVER_KEY")."");

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        
            $response = curl_exec($ch);
        
            curl_close ($ch);
            return $response;
        });
        
        return response()->json($value, 200);
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
