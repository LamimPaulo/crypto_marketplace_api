<?php

namespace App\Http\Middleware;

use App\Models\SysConfig;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class Credminer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {

        try {
            $auth = $request->header('authorization');
            $ip = $request->ip();
            $config = SysConfig::first();

            if (is_null($auth)) {
                throw new \Exception('Acesso negado');
            }

            if ($ip !== $config->ip) {
                throw new \Exception('IP inválido ['.$ip.']');
            }

            if ($auth !== $config->secret) {
                throw new \Exception('Cliente inválido');
            }

            return $next($request);

        } catch (\Exception $ex) {
            return response([
                'error' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
