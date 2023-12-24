<?php

namespace budisteikul\toursdk\Middleware;

use Closure;
use Illuminate\Http\Request;
use budisteikul\toursdk\Models\Setting;

class SettingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $settings = Setting::get();
        foreach($settings as $setting)
        {
            if($setting->name=="assets") $assets = $setting->value;
            if($setting->name=="currency") $currency = $setting->value;
            if($setting->name=="payment_enable") $payment_enable = $setting->value;
            if($setting->name=="payment_default") $payment_default = $setting->value;
            if($setting->name=="company") $company = $setting->value;
            if($setting->name=="footer") $footer = $setting->value;
            if($setting->name=="image") $image = $setting->value;
            if($setting->name=="logo") $logo = $setting->value;
            if($setting->name=="title") $title = $setting->value;
        }
        

        config(['site.assets' => $assets]);
        config(['site.currency' => $currency]);
        config(['site.payment_enable' => $payment_enable]);
        config(['site.payment_default' => $payment_default]);
        config(['site.company' => $company]);
        config(['site.footer' => $footer]);
        config(['site.image' => $image]);
        config(['site.logo' => $logo]);
        config(['site.title' => $title]);
        
        return $next($request);
    }
}
