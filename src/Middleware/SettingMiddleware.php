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
            config(['site.'.$setting->name => $setting->value]);
        }
        
        return $next($request);
    }
}
