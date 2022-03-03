<?php
namespace budisteikul\toursdk\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Models\Shoppingcart;

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
            switch($request->input('action'))
            {
            case 'BOOKING_CONFIRMED':
                if(Shoppingcart::where('confirmation_code',$data['confirmationCode'])->count()==0)
                {
                    $shoppingcart = BookingHelper::webhook_insert_shoppingcart($data);
                    BookingHelper::shoppingcart_mail($shoppingcart);
                }
                return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
            break;
            case 'BOOKING_ITEM_CANCELLED':
                $shoppingcart = Shoppingcart::where('confirmation_code',$data['confirmationCode'])->firstOrFail();
                
                $shoppingcart->booking_status = "PENDING";
                $shoppingcart->save();
                BookingHelper::confirm_payment($shoppingcart,"CANCELED");
                
                return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
            break;
            }
        }
        return response()->json([
                    "id" => "2",
                    "message" => 'Error'
                ]);
    }

}