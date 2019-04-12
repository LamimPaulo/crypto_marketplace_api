<?php

namespace App\Http\Controllers\User;

use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Models\CoinCurrentPrice;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use Symfony\Component\HttpFoundation\Response;

class UserAssetsController extends Controller
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

    public function coins()
    {
        $wallets = UserWallet::with('coin')
            ->where('user_id', auth()->user()->id)
            ->where('balance', '>' ,0)
            ->where('type', EnumUserWalletType::PRODUCT)
            ->orderBy('coin_id')
            ->get();

        $coins = [];

        $btcUsd = CoinCurrentPrice::where('coin_id', 1)->first()->price;

        foreach ($wallets as $wallet) {
            $coin = $wallet->coin;
            $btcEquivalence = $coin->id == 1 ? $wallet->balance_rounded : sprintf('%.8f', $this->btcEquivalence($wallet->balance_rounded, $coin->id));
            $usdEquivalence = $btcEquivalence * $btcUsd;
            $brlEquivalence = $this->conversorService::BTC2BRLMIN($btcEquivalence);
            $price = CoinCurrentPrice::where('coin_id', $coin->id)->first()->price;

            $coins[] = [
                'balance' => $wallet->balance_rounded,
                'price' => $coin->id == 1 ? sprintf('%.2f', $price): sprintf('%.8f', $price),
                'brl_equivalence' => number_format($brlEquivalence['amount'], '2', ',', '.'),
                'btc_equivalence' => $btcEquivalence,
                'usd_equivalence' => sprintf('%.2f', $usdEquivalence),
                'symbol' => $coin->abbr,
                'icon' => $coin->icon
            ];
        }

        return response([
            'message' => trans('messages.general.success'),
            'coins' => $coins,
            'count' => count($coins)
        ], Response::HTTP_OK);
    }

    public function coinsChart()
    {
        $wallets = UserWallet::with('coin')
            ->where('user_id', auth()->user()->id)
            ->where('balance', '>' ,0)
            ->where('type', EnumUserWalletType::PRODUCT)
            ->orderBy('coin_id')
            ->get();

        $coins = [];
        foreach ($wallets as $wallet) {
            $coin = $wallet->coin;
            $btcEquivalence = $coin->id == 1 ? $wallet->balance_rounded : sprintf('%.8f', $this->btcEquivalence($wallet->balance_rounded, $coin->id));
            array_push($coins, floatval($btcEquivalence));
        }

        return $coins;
    }

    private function btcEquivalence($value, $coin)
    {
        $quotePrice = CoinCurrentPrice::where('coin_id', $coin)->first()->price;
        return $value * $quotePrice;
    }
}
