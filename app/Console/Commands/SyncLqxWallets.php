<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\OffScreenController;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class SyncLqxWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:lqxwallets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and Sync Balance for LQX Wallets';

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

            $wallets = UserWallet::where('coin_id', Coin::getByAbbr("LQXD")->id)
                ->get();

            foreach ($wallets as $wallet) {

                DB::beginTransaction();

                $balancePercent = 0;

                if ($wallet->balance > 0) {
                    $balancePercent = $wallet->balance * 0.25;
                }

                $address = OffScreenController::post(EnumOperationType::CREATE_ADDRESS, ['amount' => $balancePercent], "LQX");

                $lqx_wallet = UserWallet::with('coin')
                    ->whereHas('coin', function ($coin) {
                        return $coin->where('abbr', 'LIKE', 'LQX');
                    })
                    ->where(['user_id' => $wallet->user_id, 'is_active' => 1])->first();

                if (!$lqx_wallet) {
                    $lqx_wallet = UserWallet::create([
                        'user_id' => $wallet->user_id,
                        'coin_id' => Coin::getByAbbr('LQX')->id,
                        'address' => $address,
                        'balance' => $balancePercent
                    ]);


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
            }

        } catch
        (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
