<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DevCheck
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

            if (!auth()->user()->is_dev) {
                throw new \Exception(trans('messages.auth.access_denied'));
            }

            return $next($request);

        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }
}
