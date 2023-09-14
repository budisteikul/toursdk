<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Support\Facades\Storage;

class LogHelper {

	public static function log_webhook($data)
    {
        $data = json_decode($data, true);
		try
            {
                Storage::disk('gcs')->put('log/webhook/'. date('YmdHis') .'.txt', json_encode($data, JSON_PRETTY_PRINT));
            }
		catch(exception $e)
            {
                
            }
    }

}
?>