<?php
namespace budisteikul\toursdk\Helpers;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class LogHelper {

    public static function log($data,$identifier="")
    {
        if(env("APP_ENV")=="local")
        {
            return "";
        }
        
        try
        {
            Storage::disk('gcs')->put('log/log-'. $identifier .'-'. date('YmdHis') .'-'.Uuid::uuid4()->toString().'.txt', json_encode($data, JSON_PRETTY_PRINT));
        }
        catch(exception $e)
        {
                
        }
    }

    public static function analytic()
    {
        if(env("APP_ENV")=="local")
        {
            return false;
        }
        return true;
    }

}
?>