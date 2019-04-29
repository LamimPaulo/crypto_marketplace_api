<?php

namespace App\Http\Controllers\User;

use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\User\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UserWalletController extends Controller
{

    public function index()
    {
        try {

            $fiat_coin = Coin::with('quote')->where('abbr', 'BRL')->first();

            $wallets = Coin::where('is_active', 1)
                ->with([
                    'quote' => function ($quote) use ($fiat_coin) {
                        return $quote->with('quote_coin')->where('quote_coin_id', $fiat_coin->id);
                    },
                    'wallets' => function ($userWallet) {
                        return $userWallet->where(['user_id' => auth()->user()->id, 'is_active' => 1, 'type' => EnumUserWalletType::WALLET]);
                    }
                ])->whereHas(
                    'wallets', function($userWallet){
                        return $userWallet->where(['user_id' => auth()->user()->id, 'is_active' => 1, 'type' => EnumUserWalletType::WALLET]);
                    }
                )->orderBy('wallet_order');

            return response([
                'message' => 'sucess',
                'wallets' => $wallets->get(),
                'count' => $wallets->count()
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function balances()
    {
        try {
            $wallets = UserWallet::with('coin')
                ->whereHas('coin', function ($coin) {
                    return $coin->where('is_active', 1);
                })->where(['user_id' => auth()->user()->id, 'is_active' => 1])
                ->orderBy('coin_id');

            return response([
                'message' => 'sucess',
                'wallets' => $wallets->get(),
                'count' => $wallets->count()
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function walletByCoin($abbr)
    {
        try {
            $wallet = UserWallet::with('coin')
                ->whereHas('coin', function ($coin) use ($abbr) {
                    return $coin->where('is_active', 1)->where('abbr', 'LIKE', $abbr);
                })
                ->where(['user_id' => auth()->user()->id, 'is_active' => 1])->first();

            if (!$wallet) {
                return response([
                    'message' => trans('messages.wallet.invalid_for_coin')
                ], Response::HTTP_NOT_FOUND);
            }

            return response([
                'message' => trans('messages.general.success'),
                'wallet' => $wallet
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function secondary()
    {
        $abbr = auth()->user()->country_id == 31 ? 'BRL' : 'USD';
        return $this->walletByCoin($abbr);
    }

    public function walletsConversionOrder()
    {
        try {
            $wallets = UserWallet::with([
                'coin' => function ($coin) {
                    return $coin->where(['is_active' => 1]);
                },
            ])
                ->whereHas('coin', function ($coin) {
                    return $coin->where(['is_active' => 1]);
                })
                ->where(['user_id' => auth()->user()->id, 'is_active' => 1, 'type' => EnumUserWalletType::WALLET])
                ->orderBy('conversion_priority')->get();

            return response([
                'message' => 'sucess',
                'wallets' => $wallets,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function walletsUpdateConversionOrder(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach ($request->wallets as $i => $w) {
                $wallet = UserWallet::findOrFail($w['id']);
                $wallet->update(['conversion_priority' => $i]);
            }
            DB::commit();

            return $this->walletsConversionOrder();

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }

}
