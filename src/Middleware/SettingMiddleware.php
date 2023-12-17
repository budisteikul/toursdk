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
        }
        

        config(['site.assets' => $assets]);
        config(['site.currency' => $currency]);
        config(['site.payment_enable' => $payment_enable]);
        config(['site.payment_default' => $payment_default]);
        config(['site.company' => $company]);
        config(['site.footer' => $footer]);
        
        return $next($request);
    }
}
