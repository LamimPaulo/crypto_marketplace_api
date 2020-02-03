<?php

namespace App\Console\Commands;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class LqxdGeneratePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lqxd:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Withdrawal for LQXD Wallets';

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

        if (Carbon::now()->format("Y-m-d") == '2020-02-16') {

            try {
                DB::beginTransaction();

                $wallets = UserWallet::with('user', 'coin')
                    ->where('coin_id', Coin::getByAbbr("LQXD")->id)
                    ->where('balance', '>', 0)
                    ->get();

                foreach ($wallets as $wallet) {

                    $output->writeln("<info>-----------------------------</info>");
                    $output->writeln("<info>{$wallet->user->email}</info>");
                    $output->writeln("<info>LQXD: {$wallet->balance}</info>");

                    $months = 12;
                    $amount = $wallet->balance / $months;

                    for ($i = 0; $i < $months; $i++) {
                        $tx = Uuid::uuid4()->toString();
                        $transaction_out = Transaction::create([
                            'user_id' => $wallet->user_id,
                            'coin_id' => $wallet->coin_id,
                            'wallet_id' => $wallet->id,
                            'toAddress' => $wallet->address,
                            'amount' => $amount,
                            'status' => EnumTransactionsStatus::SUCCESS,
                            'type' => EnumTransactionType::OUT,
                            'category' => EnumTransactionCategory::LQX_WITHDRAWAL,
                            'fee' => 0,
                            'tax' => 0,
                            'tx' => $tx,
                            'info' => '**Resgate da Migração de Contas',
                            'error' => '',
                            'is_internal' => false,
                            'payment_at' => Carbon::now()->addMonth($i)->endOfMonth()
                        ]);

                        BalanceService::decrements($transaction_out);

                        $lqx_wallet = UserWallet::with('coin')
                            ->where([
                                'user_id' => $wallet->user_id,
                                'is_active' => 1,
                                'type' => EnumUserWalletType::WALLET,
                                'coin_id' => Coin::getByAbbr("LQX")->id
                            ])->first();

                        $transaction_in = Transaction::create([
                            'user_id' => $lqx_wallet->user_id,
                            'coin_id' => $lqx_wallet->coin_id,
                            'wallet_id' => $lqx_wallet->id,
                            'toAddress' => $lqx_wallet->address,
                            'amount' => $amount,
                            'status' => EnumTransactionsStatus::PENDING,
                            'type' => EnumTransactionType::IN,
                            'category' => EnumTransactionCategory::LQX_WITHDRAWAL,
                            'fee' => 0,
                            'tax' => 0,
                            'tx' => $tx,
                            'info' => '**Resgate da Migração de Contas',
                            'error' => '',
                            'is_internal' => false,
                            'payment_at' => Carbon::now()->addMonth($i)->endOfMonth()
                        ]);

                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $output->writeln("<info>{$e->getMessage()}</info>");
                $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
                //throw new \Exception($e->getMessage());
            }
        }
    }
}
