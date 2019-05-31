<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class NationalUserNotAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (auth()->user()->country_id == 31) {
                throw new \Exception("Seu perfil não permite realizar esta ação.");
            }

            return $next($request);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
