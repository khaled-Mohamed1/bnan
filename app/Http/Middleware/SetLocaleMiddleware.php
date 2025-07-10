<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.fallback_locale');

        if ($request->hasHeader('Accept-Language')) {
            $acceptedLanguages = explode(',', $request->header('Accept-Language'));

            foreach ($acceptedLanguages as $lang) {
                $lang = strtolower(trim(explode(';', $lang)[0]));

                if (in_array($lang, ['en', 'ar'])) {
                    $locale = $lang;
                    break;
                }
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
