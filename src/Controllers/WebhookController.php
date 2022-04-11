<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Models\Shoppingcart;
use Illuminate\Support\Facades\Storage;

class WebhookController extends Controller
{

    public function __construct()
    {
        
    }

	public function webhook($webhook_app,Request $request)
    {
        if($webhook_app=="bokun")
        {
            $data = json_decode($request->getContent(), true);
            
            try
            {
                Storage::disk('gcs')->put('log/'. date('YmdHis') .'.txt', json_encode($data, JSON_PRETTY_PRINT));
            }
            catch(exception $e)
            {
                
            }

            switch($request->input('action'))
            {
            case 'BOOKING_CONFIRMED':
                if(Shoppingcart::where('confirmation_code',$data['confirmationCode'])->count()==0)
                {
                    $shoppingcart = BookingHelper::webhook_insert_shoppingcart($data);
                    BookingHelper::confirm_payment($shoppingcart,"CONFIRMED",true);
                    BookingHelper::shoppingcart_mail($shoppingcart);
                }
                return response('OK', 200)->header('Content-Type', 'text/plain');
            break;
            case 'BOOKING_ITEM_CANCELLED':
                $shoppingcart = Shoppingcart::where('confirmation_code',$data['confirmationCode'])->firstOrFail();
                
                BookingHelper::confirm_payment($shoppingcart,"CANCELED",true);
                
                return response('OK', 200)->header('Content-Type', 'text/plain');
            break;
            }
        }
        return response('ERROR', 200)->header('Content-Type', 'text/plain');
    }

}