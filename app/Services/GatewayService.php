<?php

namespace App\Services;

use App\Enum\EnumGatewayStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Models\Mining\MiningPlan;
use App\Models\Mining\MiningQuota;
use App\Models\CoinQuote;
use App\Models\GatewayStatus;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use Ramsey\Uuid\Uuid;

class GatewayService
{
    protected $balanceService;
    protected $conversorService;
    protected $taxCoinService;

    public function __construct(
        BalanceService $balance,
        ConversorService $conversor,
        TaxCoinService $taxCoin)
    {
        $this->conversorService = $conversor;
        $this->taxCoinService = $taxCoin;
        $this->balanceService = $balance;
    }

    public function setStatus($transaction, $expected)
    {
        try {
            $transaction->amount = sprintf("%.8f", $transaction->amount);
            $expected = sprintf("%.8f", $expected);

            if ($transaction->amount == $expected) {
                return EnumGatewayStatus::PAID;
            } else if ($transaction->amount < $expected) {
                return EnumGatewayStatus::UNDERPAID;
            } else if ($transaction->amount > $expected) {
                return EnumGatewayStatus::OVERPAID;
            }

            return $transaction->status;

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function updateInternal($gateway, $transaction)
    {
        try {
            $status = $this->setStatus($transaction, $gateway->amount);

            $gateway->update([
                'status' => $status,
                'txid' => Uuid::uuid4()->toString(),
                'received' => $transaction->amount,
                'is_internal_payment' => true,
                'payer_user_id' => $transaction->user_id
            ]);

            GatewayStatus::create([
                'status' => $gateway->status,
                'gateway_id' => $gateway->id
            ]);

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function updateMining($gateway)
    {
        try {
            $miningQuota = MiningQuota::firstOrNew(['user_id' => auth()->user()->id]);

            $miningPlan = MiningPlan::with(['quotas'])->first();

            $quotas_remaining = $miningPlan->ths_total - $miningPlan->quotas->sum('ths_quota');

            $ths_quantity = $gateway->fiat_amount / $miningPlan->ths_quota_price;

            if ($ths_quantity > $quotas_remaining) {
                throw new \Exception("Não é possível contratar a quantidade requisitada. No momento possuímos apenas {$quotas_remaining} Th/s disponíveis. Tente contratar um número igual ou menor a este.");
            }

            if (!$miningQuota->ths_quota) {
                $miningQuota->ths_quota = $ths_quantity;
            } else {
                $miningQuota->increment('ths_quota', $ths_quantity);
            }
            $miningQuota->mining_plan_id = 1;
            $miningQuota->buy_price = $miningPlan->ths_quota_price;
            $miningQuota->save();
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function FIAT($gateway)
    {
        try {
            $transactionIn = Transaction::create([
                'user_id' => $gateway->user_id,
                'coin_id' => $gateway->fiat_coin_id,
                'wallet_id' => UserWallet::where(['user_id' => $gateway->user_id, 'coin_id' => $gateway->fiat_coin_id, 'type' => EnumUserWalletType::WALLET])->first()->id,
                'toAddress' => '',
                'amount' => $gateway->fiat_amount,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::GATEWAY,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Gateway tx: ' . $gateway->tx,
                'error' => '',
            ]);

            TransactionStatus::create([
                'status' => $transactionIn->status,
                'transaction_id' => $transactionIn->id,
            ]);

            $this->balanceService::increments($transactionIn);

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function CRYPTO($gateway)
    {
        $tax = $gateway->amount * ($gateway->user->level->gateway_tax / 100);
        try {
            $transactionIn = Transaction::create([
                'user_id' => $gateway->user_id,
                'coin_id' => $gateway->coin_id,
                'wallet_id' => UserWallet::where(['user_id' => $gateway->user_id, 'coin_id' => $gateway->coin_id, 'type' => EnumUserWalletType::WALLET])->first()->id,
                'toAddress' => '',
                'amount' => $gateway->amount - $tax,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::GATEWAY,
                'fee' => 0,
                'tax' => $tax,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Gateway tx: ' . $gateway->tx,
                'error' => '',
            ]);

            TransactionStatus::create([
                'status' => $transactionIn->status,
                'transaction_id' => $transactionIn->id,
            ]);

            $this->balanceService::increments($transactionIn);

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
