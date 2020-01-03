<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\OffScreenController;
use App\Models\Coin;
use App\Models\LqxWithdrawal;
use App\Models\Transaction;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class LqxWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lqx:withdrawals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Withdrawal Balance for LQX Wallets';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        try {

            $withdrawal_pending = LqxWithdrawal::where('is_executed', false)->sum('percent');

            $withdrawal = LqxWithdrawal::where('is_executed', false)
                ->whereDate('date', Carbon::now()->format('Y-m-d'))
                ->first();

            if (!$withdrawal) {
                return;
            }

            $wallets = UserWallet::where('coin_id', Coin::getByAbbr("LQXD")->id)->get();

            foreach ($wallets as $wallet) {
                DB::beginTransaction();

                $balancePercent = 0;

                if ($wallet->balance > 0) {
                    $pending = LqxWithdrawal::where('is_executed', false)->count();
                    if ($pending == 1) {
                        $balancePercent = $wallet->balance;
                    } else {
                        $old_balance = ($wallet->balance * 100) / $withdrawal_pending;
                        $balancePercent = $old_balance * ($withdrawal->percent / 100);
                    }
                }

                $lqx_wallet = UserWallet::with('coin')
                    ->whereHas('coin', function ($coin) {
                        return $coin->where('abbr', 'LIKE', 'LQX');
                    })
                    ->where(['user_id' => $wallet->user_id, 'is_active' => 1])->first();

                $tx = Uuid::uuid4()->toString();

                $transaction_in = Transaction::create([
                    'user_id' => $lqx_wallet->user_id,
                    'coin_id' => $lqx_wallet->coin_id,
                    'wallet_id' => $lqx_wallet->id,
                    'toAddress' => $lqx_wallet->address,
                    'amount' => $balancePercent,
                    'status' => EnumTransactionsStatus::SUCCESS,
                    'type' => EnumTransactionType::IN,
                    'category' => EnumTransactionCategory::LQX_WITHDRAWAL,
                    'fee' => 0,
                    'tax' => 0,
                    'tx' => $tx,
                    'info' => '',
                    'error' => '',
                    'is_internal' => true,
                ]);

                BalanceService::increments($transaction_in);

                $transaction_out = Transaction::create([
                    'user_id' => $wallet->user_id,
                    'coin_id' => $wallet->coin_id,
                    'wallet_id' => $wallet->id,
                    'toAddress' => $lqx_wallet->address,
                    'amount' => $balancePercent,
                    'status' => EnumTransactionsStatus::SUCCESS,
                    'type' => EnumTransactionType::OUT,
                    'category' => EnumTransactionCategory::LQX_WITHDRAWAL,
                    'fee' => 0,
                    'tax' => 0,
                    'tx' => $tx,
                    'info' => '',
                    'error' => '',
                    'is_internal' => true,
                ]);

                BalanceService::decrements($transaction_out);

                DB::commit();
            }

            $withdrawal->is_executed = true;
            $withdrawal->save();

        } catch
        (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
