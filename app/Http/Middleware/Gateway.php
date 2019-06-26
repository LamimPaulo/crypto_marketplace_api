<?php

namespace App\Http\Middleware;

use App\Models\GatewayApiKey;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class Gateway
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

            $auth = GatewayApiKey::with(['user'])->where(['api_key' => $api_key, 'secret' => $secret])->first();

            if (is_null($auth)) {
                throw new \Exception(trans('messages.auth.invalid_client'));
            }

            if (!in_array($auth->ip, ['%', '*', ''])) {
                if ($ip !== $auth->ip) {
                    throw new \Exception(trans('messages.auth.unauthorized_ip', ['address' => $ip]));
                }
            }

            $request['user'] = $auth->user;
            $request['gateway_api_key_id'] = $auth->id;

            return $next($request);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
