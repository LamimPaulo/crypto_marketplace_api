<?php

namespace App\Console\Commands;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Models\Coin;
use App\Models\Transaction;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            $carbon = Carbon::now()->format("Y-m-d");

            $transactions = Transaction::with('user', 'coin')
                ->where([
                    'coin_id' => Coin::getByAbbr("LQX")->id,
                    'category' => EnumTransactionCategory::LQX_WITHDRAWAL,
                    'status' => EnumTransactionsStatus::PENDING
                ])
                ->where('payment_at', 'LIKE', "$carbon%")
                ->get();

            foreach ($transactions as $transaction) {
                $output->writeln("<info>-----------------------------</info>");
                $output->writeln("<info>{$transaction->user->email}</info>");
                $output->writeln("<info>{$transaction->coin->abbr}: {$transaction->amount}</info>");

                DB::beginTransaction();

                $transaction->status = EnumTransactionsStatus::SUCCESS;
                $transaction->save();

                BalanceService::increments($transaction);
                DB::commit();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
