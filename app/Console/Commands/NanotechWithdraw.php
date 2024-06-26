<?php

namespace App\Console\Commands;

use App\Enum\EnumNanotechOperationStatus;
use App\Enum\EnumNanotechOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NanotechWithdraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nanotech:withdraw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realizar saque nanotech';

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
     */
    public function handle()
    {
        $this->generate();
    }

    private function generate()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        $investments = Nanotech::with('user')->where(['type_id' => 2, 'coin_id' => 1])
            ->orderByDesc('amount')
            ->get();

        foreach ($investments as $investment) {

            try {
                $total = sprintf("%.8f", ($this->start($investment->type_id, $investment->user_id) + $this->profit($investment->type_id, $investment->user_id)));

                if ($total > 0) {
                    $output->writeln("<info>-----------------------------</info>");
                    $output->writeln("<info>{$investment->user->email}</info>");
                    $output->writeln("<info>$total</info>");

                    $balance = sprintf("%.8f", ($this->start($investment->type_id, $investment->user_id)));
                    if ($balance > 0) {
                        //saque do investimento total
                        $op1 = NanotechOperation::create([
                            'user_id' => $investment->user_id,
                            'coin_id' => $investment->coin_id,
                            'investment_id' => $investment->id,
                            'amount' => 0 - floatval($balance),
                            'status' => EnumNanotechOperationStatus::SUCCESS,
                            'type' => EnumNanotechOperationType::WITHDRAWAL,
                        ]);

                        $output->writeln("<info>Saldo: {$op1->amount}</info>");
                        $investment->amount = 0;
                        $investment->save();
                    }

                    $profit = sprintf("%.8f", ($this->profit($investment->type_id, $investment->user_id)));
                    if ($profit > 0) {
                        $op2 = NanotechOperation::create([
                            'user_id' => $investment->user_id,
                            'coin_id' => $investment->coin_id,
                            'investment_id' => $investment->id,
                            'amount' => 0 - floatval($profit),
                            'status' => EnumNanotechOperationStatus::SUCCESS,
                            'type' => EnumNanotechOperationType::PROFIT_WITHDRAWAL,
                        ]);

                        $output->writeln("<info>Lucro: {$op2->amount}</info>");
                    }


                    //credito do saque no balance
                    $wallet = UserWallet::where([
                        'coin_id' => $investment->coin_id,
                        'user_id' => $investment->user_id,
                        'type' => EnumUserWalletType::WALLET,
                    ])->first();

                    $transaction = Transaction::create([
                        'user_id' => $investment->user_id,
                        'coin_id' => $wallet->coin_id,
                        'wallet_id' => $wallet->id,
                        'toAddress' => $wallet->address,
                        'amount' => floatval($total),
                        'status' => EnumTransactionsStatus::SUCCESS,
                        'type' => EnumTransactionType::IN,
                        'category' => EnumTransactionCategory::NANOTECH,
                        'fee' => 0,
                        'tax' => 0,
                        'tx' => '',
                        'info' => 'Saque Automático Nanotech BTC',
                        'error' => '',
                    ]);

                    $output->writeln("<info>Transaction: {$transaction->amount}</info>");

                    TransactionStatus::create([
                        'transaction_id' => $transaction->id,
                        'status' => $transaction->status
                    ]);

                    BalanceService::increments($transaction);
                }
            } catch (\Exception $ex) {
                $output->writeln("<info>{$ex->getMessage()}</info>");
                $output->writeln("<info>{$ex->getLine()} - {$ex->getFile()}</info>");
            }
        }
    }

    public function start($type, $user_id)
    {
        try {
            $investments = Nanotech::where('user_id', $user_id)
                ->where('type_id', $type)
                ->get();

            return sprintf("%.8f", $investments->sum('amount'));

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function profit($type, $user_id)
    {
        try {

            $investment = Nanotech::where('type_id', $type)
                ->where('user_id', $user_id)
                ->first();

            if (!$investment) {
                return 0;
            }

            $operation = NanotechOperation::whereIn('type',
                [EnumNanotechOperationType::PROFIT, EnumNanotechOperationType::PROFIT_WITHDRAWAL, EnumNanotechOperationType::PROFIT_IN])
                ->where('user_id', $user_id)
                ->where('investment_id', $investment->id);

            return (string)sprintf("%.8f", $operation->sum('amount'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
