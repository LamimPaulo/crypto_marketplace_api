<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoinRequest;
use App\Http\Requests\UpdateLqxRequest;
use App\Models\Coin;
use App\Models\CoinQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CoinsController extends Controller
{
    public function index()
    {
        try {
            $coins = Coin::with('quote_brl')->paginate(10);
            $data = $coins->getCollection();
            $data->each(function ($item) {
                $item->makeVisible(['id']);
            });
            $coins->setCollection($data);

            return response($coins, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($abbr)
    {
        try {
            $coin = Coin::where('abbr', $abbr)->firstOrFail();
            return response([
                'status' => 'success',
                'coin' => $coin
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(CoinRequest $request)
    {
        try {
            $request['icon'] = strtolower($request->abbr) . '_icon.png';
            $request['abbr'] = strtoupper($request->abbr);
            Coin::create($request->all());

            return response([
                'status' => 'success',
                'message' => 'Moeda adicionada com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateLqx(UpdateLqxRequest $request)
    {
        try {

            $LqxToBrl= CoinQuote::where([
                'coin_id' => Coin::getByAbbr("LQX")->id,
                'quote_coin_id' => Coin::getByAbbr("BRL")->id,
            ])->first();

            $LqxToBrl->sell_quote = $request->sell_quote;
            $LqxToBrl->buy_quote = $request->buy_quote;
            $LqxToBrl->average_quote = ($request->sell_quote + $request->buy_quote) / 2;
            $LqxToBrl->save();

            $LqxToUsd = CoinQuote::where([
                'coin_id' => Coin::getByAbbr("LQX")->id,
                'quote_coin_id' => Coin::getByAbbr("USD")->id,
            ])->first();

            $usd = CoinQuote::where([
                'coin_id' => Coin::getByAbbr("USD")->id,
                'quote_coin_id' => Coin::getByAbbr("BRL")->id,
            ])->first();

            $LqxToUsd->sell_quote = $request->sell_quote / $usd->average_quote;
            $LqxToUsd->buy_quote = $request->buy_quote / $usd->average_quote;
            $LqxToUsd->average_quote = ($LqxToUsd->sell_quote + $LqxToUsd->buy_quote) / 2;
            $LqxToUsd->save();

            return response([
                'status' => 'success',
                'message' => 'LQX com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(CoinRequest $request)
    {
        try {
            $request['icon'] = strtolower($request->abbr) . '_icon.png';
            $request['abbr'] = strtoupper($request->abbr);

            $coin = Coin::findOrFail($request->id);
            $coin->update($request->all());

            Artisan::call("get:btcquote");

            return response([
                'status' => 'success',
                'message' => 'Moeda atualiza com sucesso.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function walletsOrder()
    {
        try {
            $coins = Coin::where('is_wallet', true)->orderBy('wallet_order')->get()->makeVisible('id');

            return response([
                'message' => 'sucess',
                'coins' => $coins,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function UpdateWalletsOrder(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach ($request->coins as $i => $w) {
                $wallet = Coin::findOrFail($w['id']);
                $wallet->update(['wallet_order' => $i]);
            }
            DB::commit();

            return $this->walletsOrder();

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

    }

    public static function coreLimitWithdrawal($data)
    {
        $coins = Coin::getByAbbr($data['abbr']);
        return [
            'abbr' => $coins->abbr,
            'limit_balance' => $coins->core_limit_balance,
            'limit_percent' => $coins->core_limit_percent,
            'address' => $coins->withdrawal_address,
        ];
    }

    public function wallet()
    {
        try {
            return Coin::where('is_wallet', true)->orderBy('is_crypto')->get();
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
