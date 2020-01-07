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
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        try {

            $wallets = UserWallet::with('user', 'coin')
                ->where('coin_id', Coin::getByAbbr("LQXD")->id)
                ->orderBy('balance')
                ->get();

            foreach ($wallets as $wallet) {

                if ($wallet->balance >= 0.00001) {

                    $output->writeln("<info>-----------------------------</info>");
                    $output->writeln("<info>{$wallet->user->email}</info>");
                    $output->writeln("<info>{$wallet->coin->abbr}: {$wallet->balance}</info>");

                    DB::beginTransaction();
                    $balancePercent = $wallet->balance;

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
                } else {
                    $wallet->balance = 0;
                    $wallet->save();
                }
            }

        } catch
        (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
