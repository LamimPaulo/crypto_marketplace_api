<?php

namespace App\Http\Controllers\Admin\Funds;

use App\Enum\EnumFundType;
use App\Http\Controllers\Controller;
use App\Http\Requests\FundStoreRequest;
use App\Http\Requests\FundUpdateCoinsRequest;
use App\Http\Requests\FundUpdateRequest;
use App\Models\Coin;
use App\Models\CoinCurrentPrice;
use App\Models\CoinQuote;
use App\Models\Funds\FundCoins;
use App\Models\Funds\FundOrders;
use App\Models\Funds\FundQuotes;
use App\Models\Funds\Funds;
use App\Models\Order;
use App\Services\ConversorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FundsController extends Controller
{
    protected $conversorService;

    public function __construct(
        ConversorService $conversor
    )
    {
        $this->conversorService = $conversor;
    }

    public function index()
    {
        try {
            $funds = Funds::with([
                'coins' => function ($coins) {
                    return $coins->with('coin');
                },
                'provider'
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

    public function store(FundStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $this->validateCoins($request);

            $request['type'] = EnumFundType::UNLIMITED;
            $request['value'] = $request->start_price;
            $fund = Funds::create($request->all());

            $btc_price = CoinCurrentPrice::where('coin_id', 1)->first()->price;

            if (count($request->coins)) {
                foreach ($request->coins as $coin) {
                    if ($coin['coin_id'] == 1) {
                        $fund->coins()->create([
                            'percent' => $coin['percent'],
                            'coin_id' => $coin['coin_id'],
                            'price' => $btc_price,
                            'amount' => ($request->start_amount * $coin['percent'] / 100) / $btc_price
                        ]);
                    } elseif ($coin['coin_id'] == 2) {
                        $dollar = CoinQuote::where(['coin_id' => 3, 'quote_coin_id' => 2])->first()->average_quote;
                        $fund->coins()->create([
                            'percent' => $coin['percent'],
                            'coin_id' => $coin['coin_id'],
                            'price' => $dollar,
                            'amount' => ($request->start_amount * $coin['percent'] / 100) * $dollar
                        ]);
                    } else {
                        $currentPrice = CoinCurrentPrice::where('coin_id', $coin['coin_id'])->first()->price;
                        $fund->coins()->create([
                            'percent' => $coin['percent'],
                            'coin_id' => $coin['coin_id'],
                            'price' => $btc_price * $currentPrice,
                            'amount' => ($request->start_amount * $coin['percent'] / 100) / ($btc_price * $currentPrice)
                        ]);
                    }
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
        $coins = Coin::where('abbr', '<>', 'USD')->where('is_active', true)->orderBy('name')->get()->makeVisible('id');

        return response([
            'coins' => $coins,
        ], Response::HTTP_OK);

    }

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

    public function show($fund_id)
    {
        try {
            $fund = Funds::with([
                'coins' => function ($coins) {
                    return $coins->with([
                        'coin' => function ($coin) {
                            return $coin->with('price');
                        }
                    ]);
                },
                'provider'
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

    public function update(FundUpdateRequest $request)
    {
        try {
            DB::beginTransaction();

            $fund = Funds::findOrFail($request->id);
            unset($request['start_price']);
            unset($request['start_amount']);
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

            $quote = CoinQuote::where(['coin_id' => 1, 'quote_coin_id' => 2])->first()->average_quote;

            $new_coins = [];

            foreach ($request->coins as $coin) {
                if ($coin['percent'] <= 0) {
                    throw new \Exception('A porcentagem das moedas não pode ser zerada.');
                }

                array_push($new_coins, $coin['coin_id']);
                $coin_ = FundCoins::where('fund_id', $fund->id)->where('coin_id', $coin['coin_id'])->first();

                if (!$coin_) {
                    if ($coin['coin_id'] == 1) {
                        $fund->coins()->create([
                            'percent' => $coin['percent'],
                            'coin_id' => $coin['coin_id'],
                            'price' => $quote
                        ]);
                    } elseif ($coin['coin_id'] == 2) {
                        $fund->coins()->create([
                            'percent' => $coin['percent'],
                            'coin_id' => $coin['coin_id'],
                            'price' => 1
                        ]);
                    } else {
                        $currentPrice = CoinCurrentPrice::where('coin_id', $coin['coin_id'])->first()->price;
                        $fund->coins()->create([
                            'percent' => $coin['percent'],
                            'coin_id' => $coin['coin_id'],
                            'price' => $quote * $currentPrice,
                        ]);
                    }
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

            Artisan::call("update:fundquotes");

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
        $coinsController = new CoinsController();
        $coins = $coinsController->index();
        return $coins->whereNotIn('coin_id', $coins_taken);
    }

    public function resume($fund)
    {
        try {
            $operationsBuy = FundOrders::where([
                'fund_id' => $fund,
                'side' => 'BUY'
            ])->sum('quotes');

            $operationsSell = FundOrders::where([
                'fund_id' => $fund,
                'side' => 'SELL'
            ])->sum('quotes');

            return response([
                'total_buy' => $operationsBuy,
                'total_sell' => $operationsSell,
                'available' => abs($operationsBuy - $operationsSell)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function operations(Request $request)
    {
        try {
            $operations = FundOrders::with('user')->where('fund_id', $request->fund_id)->orderBy('updated_at', 'DESC')->paginate(10);

            return response($operations, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function orders(Request $request)
    {
        try {
            $operations = Order::with('provider')->where('fund_id', $request->fund_id)->orderBy('updated_at', 'DESC')->paginate(10);

            return response($operations, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function quotes(Request $request)
    {
        try {
            $quotes = FundQuotes::with('user', 'fund')->where('fund_id', $request->fund_id)->paginate(10);

            return response($quotes, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
