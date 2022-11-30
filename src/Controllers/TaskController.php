<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use budisteikul\toursdk\Helpers\TaskHelper;
use budisteikul\toursdk\Helpers\WiseHelper;
use budisteikul\toursdk\Helpers\LogHelper;

use budisteikul\toursdk\Models\Shoppingcart;

use Illuminate\Support\Facades\Mail;
use budisteikul\toursdk\Mail\BookingConfirmedMail;

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
                if(isset($quote->error))
                {
                    return response('ERROR', 200)->header('Content-Type', 'text/plain');
                }

                $transfer = $tw->postCreateTransfer($quote->id,$data->customerTransactionId);
                if(isset($transfer->error))
                {
                    return response('ERROR', 200)->header('Content-Type', 'text/plain');
                }

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
                Mail::to($email)->cc([env("MAIL_FROM_ADDRESS")])->send(new BookingConfirmedMail($shoppingcart));
            }
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        return response('ERROR', 200)->header('Content-Type', 'text/plain');
    }
}
?>

