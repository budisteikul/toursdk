<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use budisteikul\toursdk\Helpers\TaskHelper;
use budisteikul\toursdk\Helpers\WiseHelper;
use budisteikul\toursdk\Helpers\LogHelper;

use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Models\Shoppingcart;

class TaskController extends Controller
{
	public function task(Request $request)
    {
    	LogHelper::log_webhook($request->getContent());
        
        $json = $request->getContent();
        
        TaskHelper::delete($json);

		$data = json_decode($json);

        if($data->app=="wise")
        {
            if($data->token==env('WISE_TOKEN'))
            {
                $tw = new WiseHelper();
                $quote = $tw->postCreateQuote($data->amount,$data->currency);
                $transfer = $tw->postCreateTransfer($quote->id);
                $fund = $tw->postFundTransfer($transfer->id);
                return response('OK', 200)->header('Content-Type', 'text/plain');
            }
            return response('ERROR', 200)->header('Content-Type', 'text/plain');
        }

        if($data->app=="mail")
        {
            $shoppingcart = Shoppingcart::where('session_id',$data->session_id)->where('confirmation_code',$data->confirmation_code)->first();
            $email = $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','email')->first()->answer;
            if($email!="")
            {
                Mail::to($email)->cc([self::env_mailFromAddress()])->send(new BookingConfirmedMail($shoppingcart));
            }
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        return response('ERROR', 200)->header('Content-Type', 'text/plain');
    }
}
?>

