<?php

namespace App\Http\Middleware;

use App\Models\User\UserWallet;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CheckKeycodeLevel
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
            $gateway = \App\Models\Gateway::where('address', $request->get('toAddress'))->exists();

            if ($gateway) {
                return $next($request);
            }

//            $internal = UserWallet::with('user')->where('address', $request->get('toAddress'))->first();
//            if ($internal) {
//                if ($internal->user->user_level_id == 1 OR $internal->user->user_level_id == 7 OR is_null($internal->user->api_key)) {
//                    throw new \Exception('A carteira de destino não possui keycode válido para recebimento.');
//                }
//            }
            return $next($request);

        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
