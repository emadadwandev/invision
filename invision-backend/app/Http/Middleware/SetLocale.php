<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supported = ['en', 'ar', 'fr'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        if (in_array($locale, $this->supported)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    protected function resolveLocale(Request $request): string
    {
        // 1. Query parameter ?lang=ar
        if ($request->has('lang')) {
            return $request->query('lang');
        }

        // 2. Accept-Language header
        $header = $request->header('Accept-Language', '');
        if ($header) {
            $lang = substr($header, 0, 2);
            if (in_array($lang, $this->supported)) {
                return $lang;
            }
        }

        // 3. Authenticated user preference
        $user = $request->user();
        if ($user && !empty($user->locale)) {
            return $user->locale;
        }

        // 4. Default
        return config('app.locale', 'en');
    }
}
