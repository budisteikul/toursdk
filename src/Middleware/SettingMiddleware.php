<?php

namespace budisteikul\toursdk\Middleware;

use Closure;
use Illuminate\Http\Request;
use budisteikul\toursdk\Helpers\SettingHelper;

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
        $assets = SettingHelper::getSetting('assets');
        $currency = SettingHelper::getSetting('currency');
        $payment_enable = SettingHelper::getSetting('payment_enable');
        $payment_default = SettingHelper::getSetting('payment_default');
        $company = SettingHelper::getSetting('company');
        $footer = SettingHelper::getSetting('footer');

        config(['site.assets' => $assets]);
        config(['site.currency' => $currency]);
        config(['site.payment_enable' => $payment_enable]);
        config(['site.payment_default' => $payment_default]);
        config(['site.company' => $company]);
        config(['site.footer' => $footer]);
        return $next($request);
    }
}
