<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingHelper {

    public static function getSetting($name)
    {
        $value = '';
        $setting = Setting::where('name',$name)->first();
        if($setting)
        {
            return $setting->value;
        }
        return $value;
    }

    

}
?>