<?php

namespace App\Http\Controllers\Credminer;

use App\Enum\EnumFundTransactionCategory;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckKeyRequest;
use App\Http\Requests\FundRequest;
use App\Models\Funds\FundBalances;
use App\Models\Funds\FundBalancesHists;
use App\Models\Funds\Funds;
use App\Models\Funds\FundTransaction;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class InvestmentController extends Controller
{
    public function index()
    {
        try {
            $funds = Funds::with([
                'coins' => function ($coins) {
                    return $coins->with('coin')->orderBy('percent', 'DESC');
                },
                'coin'
            ])->orderBy('is_active', 'DESC')->get();

            $products = [];

            foreach ($funds as $fund) {
                $coins = [];

                foreach ($fund->coins as $coin) {
                    $coins[] = [
                        'coin' => $coin->coin->abbr,
                        'percent' => $coin->percent,
                    ];
                }

                $products[] = [
                    'fund' => $fund->id,
                    'name' => $fund->name,
                    'redemption_tax' => $fund->redemption_tax,
                    'early_redemption_tax' => $fund->early_redemption_tax,
                    'coin' => $fund->coin->abbr,
                    'price' => $fund->price,
                    'monthly_profit' => $fund->monthly_profit,
                    'validity' => $fund->validity,
                    'is_active' => $fund->is_active,
                    'description' => $fund->description,
                    'fund_coins' => $coins
                ];
            }

            return response([
                'status' => 'success',
                'funds' => $products
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function invest(FundRequest $request)
    {
        $request->validate([
            'api_key' => 'required|exists:users,api_key'
        ]);

        try {
            if ($request->quotes < 1) {
                throw new \Exception("A cota miníma de compra deve ser superior a 0.");
            }

            $user = User::where('api_key', '=', $request->api_key)->first();
            $values = $this->estimateBuyTax($request);
            $acquired = $values['total'] - $values['tax'];
            $fund = Funds::where('is_active', true)->findOrFail($request->fund_id);

            $wallet = UserWallet::where(['user_id' => $user->id, 'coin_id' => $fund->coin_id])->first();

            DB::beginTransaction();

            $fundBalance = FundBalances::where([
                'user_id' => $user->id,
                'fund_id' => $request->fund_id
            ])->first();

            if (!$fundBalance) {
                $fundBalance = FundBalances::create([
                    'user_id' => $user->id,
                    'fund_id' => $request->fund_id,
                    'balance_free' => 0,
                    'balance_blocked' => 0,
                    'end_date' => Carbon::now()->addMonths($fund->validity)
                ]);
            }

            FundBalances::increments_blocked($fundBalance, $acquired);

            FundBalancesHists::create([
                'fund_balance_id' => $fundBalance->id,
                'balance_free' => $fundBalance->balance_free,
                'balance_blocked' => $fundBalance->balance_blocked
            ]);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'coin_id' => $wallet->coin_id,
                'wallet_id' => $wallet->id,
                'amount' => $acquired,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::FUND_CREDMINER,
                'confirmation' => 0,
                'fee' => 0,
                'tax' => $values['tax'],
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Aquisição de ' . $fund->name . ' (Credminer)',
            ]);

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            FundTransaction::create([
                'user_id' => $transaction->user_id,
                'fund_id' => $fund->id,
                'coin_id' => $transaction->coin_id,
                'transaction_id' => $transaction->id,
                'value' => $transaction->amount,
                'tax' => $transaction->tax,
                'profit_percent' => 0,
                'type' => EnumTransactionType::IN,
                'category' => EnumFundTransactionCategory::PURCHASE,
                'status' => $transaction->status,
            ]);

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

    public function estimate(FundRequest $request)
    {
        try {
            if ($request->quotes < 1) {
                throw new \Exception("A cota miníma de compra deve ser superior a 0.");
            }

            $fund = Funds::with('coin')->where('is_active', true)->findOrFail($request->fund_id);

            $price = $fund->price;

            $quotes = $request->quotes * $price;
            $tax = $quotes * ($fund->buy_tax / 100);
            $total = $quotes + $tax;

            return response([
                'status' => 'success',
                'price' => $price * 1,
                'tax' => $tax,
                'total' => $total
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function acquired(CheckKeyRequest $request)
    {
        $request->validate([
            'api_key' => 'required|exists:users,api_key'
        ]);

        $user = User::where('api_key', '=', $request->api_key)->first();

        $funds = FundBalances::with([
            'fund' => function ($fund) {
                return $fund->with([
                    'coin' => function ($coin) {
                        return $coin->get()->makeHidden('name');
                    }
                ]);
            },
        ])->where('user_id', $user->id)->orderBy('updated_at', 'DESC')->get();

        $products = [];

        foreach ($funds as $fund) {

            $products[] = [
                'fund' => $fund->fund->id,
                'name' => $fund->fund->name,
                'coin' => $fund->fund->coin->abbr,
                'start_date' => Carbon::parse($fund->created_at)->format('Y-m-d'),
                'end_date' => $fund->end_date,
                'balance_blocked' => $fund->balance_blocked,
                'balance_free' => $fund->balance_free,
            ];
        }

        return response([
            'status' => 'success',
            'funds_acquired' => $products,
        ], Response::HTTP_OK);

    }

    private function estimateBuyTax(FundRequest $request)
    {
        $fund = Funds::with('coin')->where('is_active', true)->findOrFail($request->fund_id);
        $price = $fund->price;

        $quotes = $request->quotes * $price;
        $tax = $quotes * ($fund->buy_tax / 100);
        $total = $quotes + $tax;

        return [
            'price' => $price * 1,
            'tax' => $tax,
            'total' => $total
        ];
    }
}
