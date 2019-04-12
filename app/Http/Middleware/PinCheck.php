<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class PinCheck
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
            'pin' => 'required',
        ], [
            'pin.required' => trans('validation.pin.required'),
        ]);

        if (!Hash::check($request->pin, auth()->user()->pin)) {
            return response([
                'message' => trans('messages.auth.invalid_pin'),
                'status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}
