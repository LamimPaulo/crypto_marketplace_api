<?php

namespace App\Http\Controllers\Mining;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Http\Requests\MiningBuyThsRequest;
use App\Models\Mining\MiningPlan;
use App\Models\Mining\MiningQuota;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\Services\ConversorService;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class MiningPlanController extends Controller
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

    public function buyThs(MiningBuyThsRequest $request)
    {
        try {
            $miningQuota = MiningQuota::firstOrNew(['user_id' => auth()->user()->id]);

            $miningPlan = MiningPlan::with(['quotas'])->first();

            $quotas_remaining = $miningPlan->ths_total - $miningPlan->quotas->sum('ths_quota');

            if ($request->ths_quantity > $quotas_remaining) {
                throw new \Exception(trans('messages.products.ths_sold_out', ['remaining' => $quotas_remaining]));
            }

            $amount_brl = $miningPlan->ths_quota_price * $request->ths_quantity;
            $product_coin = Coin::getByAbbr('BRL')->id;

            if ($request->payment == 1) {
                $this->balanceService->priorityConversor($amount_brl, $product_coin);
            } else {
                throw new \Exception(trans('messages.products.invalid_contract_method'));
            }

            $wallet_out = UserWallet::with('coin')->where(['user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET, 'coin_id' => $product_coin])->first();

            if (!$this->balanceService->verifyBalance($amount_brl, $wallet_out->coin->abbr)) {
                throw new \Exception(trans('messages.wallet.insuficient_balance'));
            }

            DB::beginTransaction();
            $transaction_out = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $wallet_out->coin_id,
                'wallet_id' => $wallet_out->id,
                'amount' => $amount_brl,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::MINING,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => trans('info.ths_acquired', ['amount' => $request->ths_quantity])
            ]);

            TransactionStatus::create([
                'status' => $transaction_out->status,
                'transaction_id' => $transaction_out->id,
            ]);

            $this->balanceService::decrements($transaction_out);

            if (!$miningQuota->ths_quota) {
                $miningQuota->ths_quota = $request->ths_quantity;
            } else {
                $miningQuota->increment('ths_quota', $request->ths_quantity);
            }
            $miningQuota->mining_plan_id = 1;
            $miningQuota->buy_price = $miningPlan->ths_quota_price;
            $miningQuota->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => trans('messages.products.hiring_success')
            ], Response::HTTP_OK);

        } catch (\Exception $ex) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //only for test
    private function convertCoins($miningPlan, $request)
    {
        try {
            $amount_brl = $miningPlan->ths_quota_price * $request->ths_quantity;
            $remaining = $amount_brl;

            $wallets = UserWallet::where(['user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->where('balance', '>', 0)->orderBy('conversion_priority')->get();
            $product_coin = Coin::getByAbbr('BRL')->id;

            $returnCoins[] = [
                'total' => $amount_brl
            ];

            foreach ($wallets as $wallet) {

                $quote = CoinQuote::where(['coin_id' => $wallet->coin_id, 'quote_coin_id' => $product_coin])->first();

                $balance_conversion = $wallet->balance * $quote->sell_quote;

                if (floatval($remaining) >= floatval($balance_conversion)) {
                    //converte o que tem e desconta
                    $remaining -= $balance_conversion;
                    $returnCoins[] = [
                        'coin' => $wallet->coin->abbr,
                        'quote' => $quote->sell_quote,
                        'amount_remaining' => $remaining,
                        'transaction_amount' => $balance_conversion,
                        'amount_out' => $wallet->balance,
                        'balance' => $wallet->balance
                    ];
                    $this->balanceService->conversorTransaction($wallet, $wallet->balance, $balance_conversion, $product_coin);
                } else {
                    $amount_out = $remaining / $quote->sell_quote;
                    if (!$this->balanceService->verifyBalance($amount_out, $wallet->coin->abbr)) {
                        throw new \Exception(trans('messages.wallet.insuficient_balance'));
                    }
                    $returnCoins[] = [
                        'coin' => $wallet->coin->abbr,
                        'quote' => $quote->sell_quote,
                        'amount_remaining' => $remaining,
                        'transaction_amount' => $remaining,
                        'amount_out' => $amount_out,
                        'balance' => $wallet->balance
                    ];
                    $this->balanceService->conversorTransaction($wallet, $amount_out, $remaining, $product_coin);
                    $remaining -= $remaining;
                    break;
                }
            }

            $returnCoins[] = [
                'total_remaining' => $remaining
            ];
            return $returnCoins;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

}
