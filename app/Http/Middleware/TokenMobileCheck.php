<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class TokenMobileCheck
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
        $request->validate([
            'action' => 'required|numeric',
            'code' => 'required'
        ], [
            'code.required' => trans('validation.token.code_required'),
            'action.required' => trans('validation.token.action_required'),
            'action.numeric' => trans('validation.token.action_type')
        ]);

        $TokenSmsController = new \App\Http\Controllers\Token\TokenSmsController();

        if (!$TokenSmsController->verify($request)) {
            return response([
                'message' => trans('messages.auth.invalid_token'),
                'status' => 'error'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}
