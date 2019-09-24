<?php

namespace App\Services;

use App\Enum\EnumGatewayStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
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
            $transaction->amount = sprintf("%.5f", $transaction->amount);
            $expected = sprintf("%.5f", $expected);

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
                'txid' => $transaction->tx,
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
                'category' => EnumTransactionCategory::POS,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'POS tx: ' . $gateway->tx,
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
                'category' => EnumTransactionCategory::POS,
                'fee' => 0,
                'tax' => $tax,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'POS tx: ' . $gateway->tx,
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
