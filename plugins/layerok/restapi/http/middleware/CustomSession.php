<?php

namespace Layerok\Restapi\Http\Middleware;

use Closure;
use Config;
use Illuminate\Http\Request;
use RainLab\Translate\Classes\Translator;
use Session;

class CustomSession
{
    public function handle(Request $request, Closure $next)
    {
        $session_id = $request->headers->get('X-Session-ID');

        if($session_id) {
            session()->setId($session_id);
            Session::put('cart_session_id', $session_id);
            Session::put('wishlist_session_id', $session_id);
        }

        $lang = input('lang');
        if($lang) {
            app()->setLocale($lang);
            Translator::instance()->setLocale($lang);
        }

        return $next($request);
    }

}
