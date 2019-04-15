<?php

namespace App\Http\Middleware;

use App\Models\User\UserLevel;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class PharaosGatewayElegible
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
            return $next($request);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
