<?php

use Illuminate\Database\Seeder;
use App\Models\User\UserWallet;
use App\Models\Transaction;

class UpdateTransactionInternalFlag extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transactions = Transaction::whereHas('coin', function ($coin) {
            return $coin->where('is_crypto', true);
        })
            ->where('category', \App\Enum\EnumTransactionCategory::TRANSACTION)
            ->where('type', \App\Enum\EnumTransactionType::OUT)
            ->get();

        foreach ($transactions as $transaction) {
            $exists = false;
            $exists = UserWallet::where(['address' => $transaction->toAddress, 'coin_id' => $transaction->coin_id])->exists();
            if ($exists) {
                $transaction->is_internal = true;
                $transaction->save();
            }
        }
    }
}
