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

            Storage::disk('gcs')->put('log/'. date('YmdHis') .'.txt', json_encode($data, JSON_PRETTY_PRINT));

            switch($request->input('action'))
            {
            case 'BOOKING_CONFIRMED':
                if(Shoppingcart::where('confirmation_code',$data['confirmationCode'])->count()==0)
                {
                    $shoppingcart = BookingHelper::webhook_insert_shoppingcart($data);
                    BookingHelper::confirm_payment($shoppingcart,"CONFIRMED",true);
                    BookingHelper::shoppingcart_mail($shoppingcart);
                }
                return response()->json([
                    "id" => "1",
                    "message" => 'Success'
                ]);
            break;
            case 'BOOKING_ITEM_CANCELLED':
                $shoppingcart = Shoppingcart::where('confirmation_code',$data['confirmationCode'])->firstOrFail();
                
                BookingHelper::confirm_payment($shoppingcart,"CANCELED",true);
                
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