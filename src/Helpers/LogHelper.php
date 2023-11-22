<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Support\Facades\Storage;

class LogHelper {

    public static function log($data,$identifier="")
    {
        try
            {
                Storage::disk('gcs')->put('log/log-'. $identifier .'-'. date('YmdHis') .'.txt', json_encode($data, JSON_PRETTY_PRINT));
            }
        catch(exception $e)
            {
                
            }
    }

}
?>