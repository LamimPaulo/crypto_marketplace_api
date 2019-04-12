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
            $level = UserLevel::findOrFail(auth()->user()->user_level_id);
            if(!$level->is_gateway_mmn_elegible){
                throw new \Exception(trans('messages.gateway.not_elegible'));
            }
            return $next($request);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
