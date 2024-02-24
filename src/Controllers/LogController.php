<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\LogHelper;

class LogController extends Controller
{
    
	
    public function __construct()
    {
        
    }
    
    public function log($identifier="",Request $request)
    {
        $data1 = $request->getContent();
        LogHelper::log($data1,$identifier);
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

}
