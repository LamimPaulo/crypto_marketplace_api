<?php

namespace App\Http\Controllers\Funds;

use App\Enum\EnumOrderStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Http\Requests\FundRequest;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\Funds\FundCoins;
use App\Models\Funds\FundOrders;
use App\Models\Funds\FundQuoteHists;
use App\Models\Funds\FundQuotes;
use App\Models\Funds\Funds;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class FundsController extends Controller
{
    protected $conversorService;
    protected $balanceService;

    public function __construct(
        ConversorService $conversor,
        BalanceService $balance
    )
    {
        $this->conversorService = $conversor;
        $this->balanceService = $balance;
    }

    public function index()
    {
        try {
            $funds = Funds::with([
                'coins' => function ($coins) {
                    return $coins->orderBy('percent', 'DESC')->with('coin');
                },
                'quotes'
            ])->where('is_active', true);

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

    public function pieChart($fund)
    {
        try {
            $funds = FundCoins::with(['coin'])->where('fund_id', $fund)->orderBy('percent', 'DESC')->get();
            $coins = [];
            foreach ($funds as $fund) {
                array_push($coins, (float)$fund->percent);
            }
            return $coins;

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function show($fund_id)
    {
        try {
            Funds::where('is_active', true)->findOrFail($fund_id);

            $balance = FundQuotes::where(['fund_id' => $fund_id, 'user_id' => auth()->user()->id])->first();

            if (!$balance) {
                throw new \Exception(trans('messages.products.fund_not_acquired'));
            }

            return response([
                'status' => 'success',
                'count' => 1,
                'balance' => $balance
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'count' => 0,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function buy(FundRequest $request)
    {
        try {


            $fiat = auth()->user()->country_id==31 ? "BRL" : "USD";
            $values = $this->estimateBuyTax($request);
            $fund = Funds::where('is_active', true)->findOrFail($request->fund_id);

            $product_coin = Coin::getByAbbr($fiat)->id;
            $this->balanceService->priorityConversor($values['total'], $product_coin);

            if (!$this->balanceService->verifyBalance($values['total'], $fiat)) {
                throw new \Exception(trans('messages.wallet.insuficient_balance'));
            }

            $coin_id = Coin::getByAbbr($fiat)->id;
            $wallet = UserWallet::where(['user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET, 'coin_id' => $coin_id])->first();

            DB::beginTransaction();
            $fundQuote = FundQuotes::firstOrNew([
                'user_id' => auth()->user()->id,
                'fund_id' => $request->fund_id
            ]);

            $fundQuote->value = $values['price'];
            $fundQuote->save();

            $fundQuote->quote = $request->quotes;
            $fundQuote->amount = $values['total'] - $values['tax'];

            FundQuotes::increments($fundQuote);

            FundQuoteHists::create([
                'user_id' => auth()->user()->id,
                'fund_id' => $request->fund_id,
                'quote' => $request->quotes,
                'value' => $values['price'],
                'amount' => $fundQuote->amount
            ]);

            FundOrders::create([
                'user_id' => auth()->user()->id,
                'fund_id' => $fund->id,
                'side' => 'BUY',
                'quotes_executed' => 0,
                'quotes' => $request->quotes,
                'admin_tax' => 0,
                'tax' => $values['tax'],
                'is_executed' => 0,
                'value' => $values['total'] - $values['tax'],
            ]);

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $coin_id,
                'wallet_id' => $wallet->id,
                'coin_provider_id' => $fund->coin_provider_id,
                'amount' => $values['total'] - $values['tax'],
                'status' => EnumOrderStatus::FILLED,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::INDEX_FUND,
                'confirmation' => 0,
                'fee' => 0,
                'tax' => $values['tax'],
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Aquisição de ' . $fund->name,
            ]);

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $this->balanceService::decrements($transaction);

            DB::commit();
            return response([
                'status' => 'success',
                'message' => trans('messages.products.hiring_success'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function sell(FundRequest $request)
    {
        try {
            DB::beginTransaction();

            $values = $this->estimateSellTax($request);
            $fund = Funds::where('is_active', true)->findOrFail($request->fund_id);

            $fundQuote = FundQuotes::where(['user_id' => auth()->user()->id, 'fund_id' => $request->fund_id])->firstOrFail();

            if ($request->quotes > $fundQuote->quote) {
                throw new \Exception(trans('messages.products.insuficient_profit'));
            }

            $fiat = auth()->user()->country_id==31 ? "BRL" : "USD";
            $coin_id = Coin::getByAbbr($fiat)->id;
            $wallet = UserWallet::where(['user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET, 'coin_id' => $coin_id])->first();

            $fundQuote->quote -= $request->quotes;
            $fundQuote->amount -= $values['total'];

            if ($fundQuote->amount < 0) {
                $fundQuote->amount = 0;
            }

            $fundQuote->save();

            FundQuoteHists::create([
                'user_id' => auth()->user()->id,
                'fund_id' => $request->fund_id,
                'quote' => $request->quotes,
                'value' => $values['price'],
                'amount' => -$values['total']
            ]);

            FundOrders::create([
                'user_id' => auth()->user()->id,
                'fund_id' => $fund->id,
                'side' => 'SELL',
                'quotes_executed' => 0,
                'quotes' => $request->quotes,
                'admin_tax' => $values['admin_tax'],
                'tax' => $values['tax'],
                'is_executed' => 0,
                'value' => $values['total'],
            ]);

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $coin_id,
                'wallet_id' => $wallet->id,
                'coin_provider_id' => $fund->coin_provider_id,
                'amount' => $values['total'],
                'status' => EnumOrderStatus::FILLED,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::INDEX_FUND,
                'confirmation' => 0,
                'fee' => 0,
                'tax' => $values['tax'] + $values['admin_tax'],
                'tx' => Uuid::uuid4()->toString(),
                'error' => 'Venda de ' . $fund->name,
            ]);

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $transaction->tax = 0;
            $this->balanceService::increments($transaction);

            DB::commit();
            return response([
                'status' => 'success',
                'message' => trans('messages.products.index_fund_sold_success'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function estimateBuyTax(FundRequest $request)
    {
        $fund = Funds::where('is_active', true)->findOrFail($request->fund_id);

        $price = $fund->value;

        if (auth()->user()->country_id != 31) {
            $dollar = CoinQuote::where(['coin_id' => 3, 'quote_coin_id' => 2])->first()->average_quote;
            $price = $fund->value / $dollar;
        }

        $quotes = $request->quotes * $price;
        $tax = $quotes * ($fund->buy_tax / 100);

        return [
            'price' => $price * 1,
            'tax' => $tax,
            'total' => $quotes + $tax
        ];
    }

    public function estimateSellTax(FundRequest $request)
    {
        $fund = Funds::where('is_active', true)->findOrFail($request->fund_id);

        $price = $fund->value;

        if (auth()->user()->country_id != 31) {
            $dollar = CoinQuote::where(['coin_id' => 3, 'quote_coin_id' => 2])->first()->average_quote;
            $price = $fund->value / $dollar;
        }

        $quotes = $request->quotes * $price;
        $tax = $quotes * ($fund->sell_tax / 100);
        $total = $quotes - $tax;
        $admin_tax = 0;

        $balance = FundQuotes::where(['fund_id' => $request->fund_id, 'user_id' => auth()->user()->id])->first();

        if ($balance) {
            if ($total > $balance->amount) {
                $diff = $total - $balance->amount;
                $admin_tax = $diff * ($fund->admin_tax / 100);
            }
        }

        return [
            'price' => $price * 1,
            'tax' => $tax,
            'admin_tax' => $admin_tax,
            'total' => $total - $admin_tax
        ];
    }

    public function userList()
    {
        try {
            $funds = FundQuotes::with(['fund'])->where('user_id', auth()->user()->id)->orderBy('fund_id')->get();

            $chart = [];
            foreach ($funds as $fund) {
                array_push($chart, (float)$fund->quote);
            }

            return response([
                'status' => 'success',
                'chart' => $chart,
                'funds' => $funds,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
