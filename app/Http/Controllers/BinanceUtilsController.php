<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\CoinCurrentPrice;
use App\Models\CoinPrice;
use App\Services\BalanceService;
use App\Services\ConversorService;
use Symfony\Component\HttpFoundation\Response;

class BinanceUtilsController extends Controller
{
    protected $balanceService;
    protected $conversorService;

    public function __construct(
        BalanceService $balance,
        ConversorService $conversor)
    {
        $this->conversorService = $conversor;
        $this->balanceService = $balance;
    }

    public function getPrices()
    {
        $coins = Coin::whereNotIn('abbr', ['BTC', 'BRL', 'USD'])->where('is_asset', true)->where('is_active', true)->get();
        $prices_ = [];

        foreach ($coins as $coin) {
            $price = CoinPrice::where('symbol', $coin->abbr)->first();
            $curPrice = CoinCurrentPrice::where('symbol', $coin->abbr)->first();
            $prices_[$coin->abbr] = [
                'coin_id' => $coin->id,
                'icon' => $coin->icon,
                'symbol' => $coin->abbr,
                'name' => $coin->name,
                'price' => $curPrice->price,
                'price24' => $price->last_price,
                'price_change_percent' => $price->price_change_percent
            ];
        }
        return response([
            'prices' => $prices_,
        ], Response::HTTP_OK);

    }

    public function getPrice($symbol)
    {
        $price = CoinPrice::where('symbol', $symbol)->first();
        $curPrice = CoinCurrentPrice::where('symbol', $symbol)->first();

        return response([
            'price' => $curPrice->price,
            'price24' => $price->last_price,
            'price_change_percent' => $price->price_change_percent
        ], Response::HTTP_OK);
    }


}
