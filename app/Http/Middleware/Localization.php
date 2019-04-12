<?php

namespace App\Http\Middleware;

use Closure;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check user country and determine localizaton
        $local = auth()->user()->country_id !== 31 ? 'en' : 'pt_BR';

        // set laravel localization
        app()->setLocale($local);

        // continue request
        return $next($request);
    }
}