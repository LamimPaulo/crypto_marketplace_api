<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class WithdrawalAllowed
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
            $user = User::findOrFail(auth()->user()->id);

            if ($user->timezoneSettings['withdrawal_day'] && $user->timezoneSettings['withdrawal_time']) {
                return $next($request);
            }

            throw new \Exception(trans('messages.withdrawal.day_off'));
        } catch (\Exception $ex) {
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
