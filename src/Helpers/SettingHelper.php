<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Setting;

class SettingHelper {

    public static function getSetting($name)
    {
        $setting = Setting::where('name',$name)->first();
        return $setting->value;
    }

    

}
?>