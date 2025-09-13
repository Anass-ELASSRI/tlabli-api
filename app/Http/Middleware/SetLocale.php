<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', 'fr');

        // Check if the locale is available
        if (!in_array($locale, config('app.available_locales'))) {
            $locale = 'fr'; // Default to 'en' if not available
        }

        App::setLocale($locale);

        return $next($request);
    }
}
