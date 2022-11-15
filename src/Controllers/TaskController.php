<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use budisteikul\toursdk\Helpers\LogHelper;

class TaskController extends Controller
{
	public function task(Request $request)
    {
    	LogHelper::log_webhook($request->getContent());
        $json = $request->getContent();
		$data = json_decode($json);
    }
}
?>

