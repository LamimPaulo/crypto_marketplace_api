<?php

namespace App\Http\Middleware;

use App\Models\Coin;
use App\Models\User\UserLevel;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class AllowSellForFiat
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

            $quote = Coin::getByAbbr($request->quote)->is_crypto;
            $base = Coin::getByAbbr($request->base)->is_crypto;

            if(!$quote) {
                if (!$level->is_allowed_sell_by_fiat) {
                    throw new \Exception(trans('messages.products.not_allowed_sell_by_fiat'));
                }
            }

            if(!$base) {
                if (!$level->is_allowed_buy_with_fiat) {
                    throw new \Exception(trans('messages.products.not_allowed_buy_with_fiat'));
                }
            }

            return $next($request);
        } catch (\Exception $ex) {
            return response([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
