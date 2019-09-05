<?php

namespace App\Http\Middleware;

use App\Models\SysConfig;
use Closure;
use Illuminate\Http\Request;
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
    public function handle(Request $request, Closure $next)
    {

        try {
            $auth = $request->header('authorization');
            $ip = $request->ip();
            $config = SysConfig::where('ip', $ip)->first();

            if (!$config) {
                throw new \Exception('IP inválido ['.$ip.']');
            }

            if (is_null($auth)) {
                throw new \Exception('Acesso negado');
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
