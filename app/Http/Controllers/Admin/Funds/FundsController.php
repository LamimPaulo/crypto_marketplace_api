<?php

namespace App\Http\Controllers\Admin\Funds;

use App\Enum\EnumFundTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\FundStoreRequest;
use App\Http\Requests\FundUpdateCoinsRequest;
use App\Http\Requests\FundUpdateRequest;
use App\Models\Coin;
use App\Models\Funds\FundBalances;
use App\Models\Funds\FundBalancesHists;
use App\Models\Funds\FundCoins;
use App\Models\Funds\Funds;
use App\Models\Funds\FundTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FundsController extends Controller
{
    public function index()
    {
        try {
            $funds = Funds::with([
                'coins' => function ($coins) {
                    return $coins->with('coin');
                },
                'coin'
            ])->orderBy('is_active', 'DESC');

            return response([
                'status' => 'success',
                'count' => $funds->count(),
                'funds' => $funds->get()
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param FundStoreRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(FundStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $this->validateCoins($request);

            $fund = Funds::create($request->all());

            if (count($request->coins)) {
                foreach ($request->coins as $coin) {
                    $fund->coins()->create([
                        'percent' => $coin['percent'],
                        'coin_id' => $coin['coin_id'],
                    ]);
                }
            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Fundo Criado com Sucesso!',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function coins()
    {
        $coins = Coin::where('is_active', true)->orderBy('name')->get()->makeVisible('id');

        return response([
            'coins' => $coins,
        ], Response::HTTP_OK);

    }

    /**
     * @param $request
     * @throws \Exception
     */
    private function validateCoins($request)
    {
        $total_percent = 0;
        foreach ($request->coins as $coin) {
            $total_percent += $coin['percent'];
        }

        if ($total_percent != 100) {
            throw new \Exception("A composição do fundo deve chegar aos 100% (atualmente: $total_percent%)");
        }
    }

    /**
     * @param $fund_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function show($fund_id)
    {
        try {

            $fund = Funds::with([
                'coins' => function ($coins) {
                    return $coins->with('coin');
                },
                'coin'
            ])->findOrFail($fund_id);

            return response([
                'message' => trans('messages.general.success'),
                'fund' => $fund
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function update(FundUpdateRequest $request)
    {
        try {
            DB::beginTransaction();

            $fund = Funds::findOrFail($request->id);
            $fund->update($request->all());

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Fundo alterado com Sucesso!',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateCoins(FundUpdateCoinsRequest $request)
    {
        try {
            DB::beginTransaction();

            $this->validateCoins($request);

            $fund = Funds::findOrFail($request->id);

            $new_coins = [];

            foreach ($request->coins as $coin) {
                if ($coin['percent'] <= 0) {
                    throw new \Exception('A porcentagem das moedas não pode ser zerada.');
                }

                array_push($new_coins, $coin['coin_id']);
                $coin_ = FundCoins::where('fund_id', $fund->id)->where('coin_id', $coin['coin_id'])->first();

                if (!$coin_) {
                    $fund->coins()->create([
                        'percent' => $coin['percent'],
                        'coin_id' => $coin['coin_id'],
                    ]);
                } else {
                    $coin_->update([
                        'percent' => $coin['percent'],
                    ]);
                }

                unset($coin_);
            }

            $delete = FundCoins::where('fund_id', $fund->id)->whereNotIn('coin_id', $new_coins)->get();
            foreach ($delete as $d) {
                $d->delete();
            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Fundo alterado com Sucesso!',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function remaining(Request $request)
    {
        $coins_taken = [];
        foreach ($request->coins as $coin) {
            if (isset($coin['coin_id'])) {
                array_push($coins_taken, $coin['coin_id']);
            }
        }
        return Coin::whereNotIn('coin_id', $coins_taken)->get();
    }

    /**
     * @param $fund
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function resume($fund)
    {
        try {
            $profits = FundTransaction::where([
                'fund_id' => $fund,
                'type' => EnumTransactionType::IN,
                'category' => EnumFundTransactionCategory::PROFIT,
                'status' => EnumTransactionsStatus::SUCCESS
            ])->sum('value');

            $sales = FundTransaction::where([
                'fund_id' => $fund,
                'type' => EnumTransactionType::IN,
                'category' => EnumFundTransactionCategory::PURCHASE,
                'status' => EnumTransactionsStatus::SUCCESS
            ])->sum('value');

            return response([
                'total_sale' => $sales,
                'total_profits' => $profits,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function expirationCommand()
    {
        try {
            $balances = FundBalances::with('fund')
                ->where('end_date', Carbon::today())
                ->where('balance_blocked', '>', 0)
                ->get();

            foreach ($balances as $balance) {
                FundTransaction::create([
                    'user_id' => $balance->user_id,
                    'fund_id' => $balance->fund_id,
                    'coin_id' => $balance->fund->coin_id,
                    'value' => $balance->balance_blocked,
                    'tax' => 0,
                    'profit_percent' => 0,
                    'type' => EnumTransactionType::IN,
                    'category' => EnumFundTransactionCategory::WITHDRAWAL,
                    'status' => EnumTransactionsStatus::SUCCESS,
                ]);

                FundBalances::decrements_blocked($balance, $balance->balance_blocked);
                FundBalances::increments_free($balance, $balance->balance_blocked);

                $newBalance = FundBalances::find($balance->id);
                FundBalancesHists::create([
                    'fund_balance_id' => $balance->id,
                    'balance_free' => $newBalance->balance_free,
                    'balance_blocked' => $newBalance->balance_blocked
                ]);

                $balance->end_date = Carbon::today()->addMonths($balance->fund->validity);
                $balance->save();
            }
        }catch (\Exception $e){
            return $e;
        }
    }
}
