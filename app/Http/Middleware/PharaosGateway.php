<?php

namespace App\Http\Middleware;

use App\Models\PharaosGatewayApiKey;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class PharaosGateway
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
            $ip = $request->ip();
            $api_key = $request->get('api_key');
            $secret = $request->header('Authorization');

            if (is_null($api_key) OR is_null($secret)) {
                throw new \Exception(trans('messages.auth.access_denied'));
            }

            $auth = PharaosGatewayApiKey::with(['user'])->where(['api_key' => $api_key, 'secret' => $secret])->first();

            if (is_null($auth)) {
                throw new \Exception(trans('messages.auth.invalid_client'));
            }

            if ($ip !== $auth->ip) {
                throw new \Exception(trans('messages.auth.unauthorized_ip', ['address' => $ip]));
            }

            $request['user'] = $auth->user;

            return $next($request);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
