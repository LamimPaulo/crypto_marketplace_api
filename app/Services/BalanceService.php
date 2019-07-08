<?php

namespace App\Services;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Helpers\Validations;
use App\Http\Controllers\OffScreenController;
use App\Models\Coin;
use App\Models\CoinQuote;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserBalanceHist;
use App\Models\User\UserWallet;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class BalanceService
{
    /**
     * @param Request $request
     * $request->fromAddress
     * $request->toAddress
     * $request->amount
     * @return array
     */
    public static function withDrawFee(Request $request)
    {
        $address = UserWallet::with('coin')->where('address', '=', $request->fromAddress)->firstOrFail();

        $user = User::findOrFail($address->user_id);

        if (auth()->user()->id === env("NAVI_USER")) {
            $tax = 0;
        } else {
            $tax = TaxCoinService::sumTaxSendCrypto($user->user_level_id, $request->amount);
        }


        //verificacao de transacao interna por email ou address
        $to = UserWallet::where(['address' => $request->toAddress, 'type' => EnumUserWalletType::WALLET])->first();
        $isValid = Validations::validEmail($request->toAddress);
        if ($isValid) {
            $user = User::where('email', $request->toAddress)->first();
            if ($user) {
                $to = UserWallet::where(['user_id' => $user->id, 'coin_id' => $address->coin->id, 'type' => EnumUserWalletType::WALLET])->first();
            }
        }

        if ($to) {
            return [
                'fromAddress' => $request->fromAddress,
                'toAddress' => $request->toAddress,
                'amount' => (float)$request->amount,
                'fee' => sprintf('%.' . $address->coin->decimal . 'f', 0),
                'tax' => sprintf('%.' . $address->coin->decimal . 'f', 0)
            ];
        }


        $fee = self::estimateFeeBTC($address->coin->id, $request->priority);

        return [
            'fromAddress' => $request->fromAddress,
            'toAddress' => $request->toAddress,
            'amount' => (float)$request->amount,
            'fee' => sprintf('%.' . $address->coin->decimal . 'f', $fee),
            'tax' => sprintf('%.' . $address->coin->decimal . 'f', floatval($tax)),
            'level_id' => $user->user_level_id
        ];
    }

    public static function estimateFeeBTC($coin, $priority)
    {
        try {
            $fee = self::getPritory($priority);
            return Coin::where('id', $coin)->first()->{$fee};
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private static function getPritory($p)
    {
        switch ($p) {
            case 1:
                return 'fee_high';
                break;
            case 3:
                return 'fee_avg';
                break;
            case 6:
                return 'fee_low';
                break;
        }
    }

    public static function increments($transaction)
    {
        try {
            DB::beginTransaction();

            $wallet = UserWallet::with('coin')->where('user_id', '=', $transaction->user_id)
                ->where('coin_id', '=', $transaction->coin_id)
                ->where('id', '=', $transaction->wallet_id);

            self::hist($wallet->first(), $transaction, 'increment');

            $wallet->increment('balance', sprintf("%.8f", $transaction->amount));

            if ($wallet->first()->coin->is_crypto AND $wallet->first()->coin->abbr != "LQX" AND env("APP_ENV") != "local") {
                OffScreenController::post(EnumOperationType::INCREMENT_BALANCE, ['address' => $wallet->first()->address, 'amount' => sprintf("%.8f", $transaction->amount)], $wallet->first()->coin->abbr);
            }


            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public static function decrements($transaction)
    {
        try {
            DB::beginTransaction();

            $amount = floatval($transaction->amount);
            $fee = floatval($transaction->fee);
            $tax = floatval($transaction->tax);
            $total = sprintf("%.8f", $amount + $fee + $tax);

            $wallet = UserWallet::with('coin')->where('user_id', '=', $transaction->user_id)
                ->where('coin_id', '=', $transaction->coin_id)
                ->where('id', '=', $transaction->wallet_id);

            self::hist($wallet->first(), $transaction, 'decrement');
            $wallet->decrement('balance', sprintf("%.8f", (string)$total));

            if ($wallet->first()->coin->is_crypto AND $wallet->first()->coin->abbr != "LQX" AND env("APP_ENV") != "local") {
                OffScreenController::post(EnumOperationType::DECREMENT_BALANCE, ['address' => $wallet->first()->address, 'amount' => $total], $wallet->first()->coin->abbr);
            }

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public static function reverse($transaction)
    {
        try {
            DB::beginTransaction();

            $amount = floatval($transaction->amount);
            $fee = floatval($transaction->fee);
            $tax = floatval($transaction->tax);
            $total = sprintf("%.8f", $amount + $fee + $tax);

            $wallet = UserWallet::with('coin')->where('user_id', '=', $transaction->user_id)
                ->where('coin_id', '=', $transaction->coin_id)
                ->where('id', '=', $transaction->wallet_id);

            self::hist($wallet->first(), $transaction, 'reverse');
            $wallet->increment('balance', (string)$total);

            if ($wallet->first()->coin->is_crypto AND $wallet->first()->coin->abbr != "LQX" AND env("APP_ENV") != "local") {
                OffScreenController::post(EnumOperationType::INCREMENT_BALANCE, ['address' => $wallet->first()->address, 'amount' => $total], $wallet->first()->coin->abbr);
            }

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function verifyBalance($amount, $abbr, $type = null, $user_id = null)
    {
        if (is_null($user_id)) {
            $user_id = auth()->user()->id;
        }

        if (is_null($type)) {
            $type = EnumUserWalletType::WALLET;
        }

        $coin = Coin::getByAbbr($abbr);
        $from = UserWallet::where(['coin_id' => $coin->id, 'user_id' => $user_id, 'type' => $type])->first();

        if ((float)$from->balance >= (float)$amount) {
            return true;
        }

        return false;
    }

    public function priorityConversor($amount, $product_coin)
    {
        try {
            DB::beginTransaction();
            $wallets = UserWallet::with('coin')->where(['user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET])->where('balance', '>', 0)->orderBy('conversion_priority')->get();
            $remaining = $amount;

            foreach ($wallets as $wallet) {

                $quote = CoinQuote::where(['coin_id' => $wallet->coin_id, 'quote_coin_id' => $product_coin])->first();

                $balance_conversion = $wallet->balance * $quote->sell_quote;

                if (floatval($remaining) >= floatval($balance_conversion)) {
                    $remaining -= $balance_conversion;
                    $this->conversorTransaction($wallet, $wallet->balance, $balance_conversion, $product_coin);
                } else {
                    $amount_out = $remaining / $quote->sell_quote;
                    if (!$this->verifyBalance($amount_out, $wallet->coin->abbr)) {
                        throw new \Exception(trans('messages.wallet.insuficient_balance'));
                    }
                    $this->conversorTransaction($wallet, $amount_out, $remaining, $product_coin);
                    $remaining -= $remaining;
                    break;
                }
            }

            if ($remaining > 0) {
                throw new \Exception(trans('messages.wallet.insuficient_balances'));
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $wallet
     * @param $amount_out
     * @param $amount_in
     * @param $product_coin
     * @throws \Exception
     */
    public function conversorTransaction($wallet, $amount_out, $amount_in, $product_coin)
    {
        try {
            if ($wallet->coin_id == $product_coin) {
                return;
            }

            if ($amount_out == 0 || $amount_in == 0) {
                return;
            }

            $transaction_out = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $wallet->coin_id,
                'wallet_id' => $wallet->id,
                'amount' => $amount_out,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Conversão automática para Compra'
            ]);

            TransactionStatus::create([
                'status' => $transaction_out->status,
                'transaction_id' => $transaction_out->id,
            ]);

            $wallet_in = UserWallet::where(['user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET, 'coin_id' => $product_coin])->first();
            $transaction_in = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $wallet_in->coin_id,
                'wallet_id' => $wallet_in->id,
                'amount' => $amount_in,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::CONVERSION,
                'fee' => 0,
                'tax' => 0,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Conversão automática para Compra'
            ]);

            TransactionStatus::create([
                'status' => $transaction_in->status,
                'transaction_id' => $transaction_in->id,
            ]);

            self::decrements($transaction_out);
            self::increments($transaction_in);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function hist($wallet, $transaction, $type)
    {
        UserBalanceHist::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'coin_id' => $wallet->coin_id,
            'address' => $wallet->address,
            'transaction_id' => $transaction->id ?? null,
            'amount' => $transaction->amount,
            'fee' => $transaction->fee,
            'tax' => $transaction->tax,
            'balance' => $wallet->balance,
            'type' => $type,
        ]);
    }
}



