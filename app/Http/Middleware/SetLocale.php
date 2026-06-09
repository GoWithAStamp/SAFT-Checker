<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware that sets the application locale from the user's session.
 *
 * Reads the "locale" key from the session (defaulting to the app's configured locale)
 * and applies it if it is one of the supported languages (pt, en). This drives
 * all __() and trans() calls for the current request.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if (in_array($locale, ['pt', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
