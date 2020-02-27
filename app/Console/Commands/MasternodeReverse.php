<?php

namespace App\Console\Commands;

use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Models\Masternode;
use App\Models\MasternodeUserPlan;
use App\Models\Transaction;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class MasternodeReverse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masternode:reverse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Estorno de Masternodes Bugados';

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
        if (env("APP_ENV") == 'local') {
            $this->reverseSuspended();
            $this->reverseErrors();
        }
    }

    private function reverseSuspended()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        try {
            DB::beginTransaction();

            $suspensos = DB::table('masternodes_suspensos')->get();

            foreach ($suspensos as $suspenso) {
                $masternode = Masternode::find($suspenso->id);
                $supendedDate = $masternode->updated_at;

                $output->writeln("<info>______________________________</info>");
                $output->writeln("<info>{$suspenso->email}</info>");
                $output->writeln("<info>{$masternode->payment_address}</info>");

                if ($masternode) {
                    $masternode->label = 'Devolução de Pagamento: 27/02/2020';
                    $masternode->save();

                    $wallet = UserWallet::where([
                        'user_id' => $masternode->user_id,
                        'coin_id' => $masternode->coin_id,
                        'type' => EnumUserWalletType::WALLET,
                    ])->firstOrFail();

                    $payment = Transaction::where([
                        'toAddress' => $masternode->payment_address,
                        'category' => EnumTransactionCategory::MASTERNODE_UNDO,
                        'user_id' => $masternode->user_id,
                        'type' => EnumTransactionType::IN,
                        'tx' => $suspenso->tx
                    ])->first();

                    if (!$payment) {
                        $payment_out = Transaction::where([
                            'toAddress' => $masternode->payment_address,
                            'category' => EnumTransactionCategory::TRANSACTION,
                            'user_id' => $masternode->user_id,
                            'type' => EnumTransactionType::OUT,
                            'tx' => $suspenso->tx
                        ])->first();

                        if (!$payment_out) {
                            $output->writeln("<info>{$suspenso->tx}</info>");
                            continue;

                        }
                        $newTransaction = $payment_out->replicate();
                        $newTransaction->info = "Desfazimento Masternode Suspenso";
                        $newTransaction->type = EnumTransactionType::IN;
                        $newTransaction->category = EnumTransactionCategory::MASTERNODE_UNDO;
                        $newTransaction->created_at = Carbon::now();
                        $newTransaction->updated_at = Carbon::now();
                        $newTransaction->save();
                        BalanceService::increments($newTransaction);

                    }

                    $plans = MasternodeUserPlan::where([
                        'user_id' => $masternode->user_id,
                        'masternode_id' => $masternode->id,
                        'status' => EnumMasternodeStatus::SUCCESS
                    ])
                        ->where('end_date', ">", $supendedDate)
                        ->orderBy('end_date')
                        ->get();

                    if (!count($plans)) {
                        continue;
                    }

                    foreach ($plans as $plan) {
                        $output->writeln("<info>Devolução: $plan->startDateLocal à $plan->endDateLocal</info>");

                        $transaction = Transaction::where([
                            'toAddress' => $masternode->payment_address,
                            'category' => EnumTransactionCategory::MASTERNODE,
                            'status' => EnumTransactionsStatus::SUCCESS,
                            'user_id' => $masternode->user_id,
                            'type' => EnumTransactionType::IN,
                            'amount' => 125,
                            'info' => 'Devolução: ' . $plan->startDateLocal . ' à ' . $plan->endDateLocal,
                        ])->first();

                        if ($transaction) {
                            continue;
                        }

                        $plan->status = EnumMasternodeStatus::CANCELED;
                        $plan->save();

                        $chargeback = Transaction::create([
                            'user_id' => $masternode->user_id,
                            'coin_id' => $masternode->coin_id,
                            'wallet_id' => $wallet->id,
                            'toAddress' => $masternode->payment_address,
                            'amount' => 125,
                            'status' => EnumTransactionsStatus::SUCCESS,
                            'type' => EnumTransactionType::IN,
                            'category' => EnumTransactionCategory::MASTERNODE,
                            'confirmation' => 0,
                            'tax' => 0,
                            'tx' => Uuid::uuid4()->toString(),
                            'info' => 'Devolução: ' . $plan->startDateLocal . ' à ' . $plan->endDateLocal,
                            'error' => '',
                            'market' => '',
                            'price' => '',
                        ]);

                        BalanceService::increments($chargeback);
                    }

                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }

    private function reverseErrors()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        try {
            DB::beginTransaction();

            $nao_ativos = DB::table('masternodes_nao_ativos')->get();

            foreach ($nao_ativos as $nao_ativo) {
                $masternode = Masternode::find($nao_ativo->id);
                $supendedDate = $masternode->updated_at;

                $output->writeln("<info>______________________________</info>");
                $output->writeln("<info>{$nao_ativo->email}</info>");

                if ($masternode) {
                    $masternode->label = 'Devolução de Pagamento: 27/02/2020';
                    $masternode->status = EnumMasternodeStatus::CANCELED;
                    $masternode->save();

                    $wallet = UserWallet::where([
                        'user_id' => $masternode->user_id,
                        'coin_id' => $masternode->coin_id,
                        'type' => EnumUserWalletType::WALLET,
                    ])->firstOrFail();

                    $payment = Transaction::where([
                        'toAddress' => $masternode->payment_address,
                        'category' => EnumTransactionCategory::MASTERNODE_UNDO,
                        'user_id' => $masternode->user_id,
                        'type' => EnumTransactionType::IN,
                    ])->first();

                    if (!$payment) {
                        $payment_out = Transaction::where([
                            'toAddress' => $masternode->payment_address,
                            'category' => EnumTransactionCategory::TRANSACTION,
                            'user_id' => $masternode->user_id,
                            'type' => EnumTransactionType::OUT,
                        ])->first();

                        if (!$payment_out) {
                            $output->writeln("<info>{$masternode->payment_address}</info>");
                            continue;
                        }

                        $newTransaction = $payment_out->replicate();
                        $newTransaction->info = "Desfazimento Masternode Suspenso";
                        $newTransaction->type = EnumTransactionType::IN;
                        $newTransaction->category = EnumTransactionCategory::MASTERNODE_UNDO;
                        $newTransaction->created_at = Carbon::now();
                        $newTransaction->updated_at = Carbon::now();
                        $newTransaction->save();
                        BalanceService::increments($newTransaction);

                    }

                    $plans = MasternodeUserPlan::where([
                        'user_id' => $masternode->user_id,
                        'masternode_id' => $masternode->id,
                        'status' => EnumMasternodeStatus::SUCCESS
                    ])
                        ->where('end_date', ">", $supendedDate)
                        ->orderBy('end_date')
                        ->get();

                    if (!count($plans)) {
                        continue;
                    }

                    foreach ($plans as $plan) {
                        $output->writeln("<info>Devolução: $plan->startDateLocal à $plan->endDateLocal</info>");

                        $transaction = Transaction::where([
                            'toAddress' => $masternode->payment_address,
                            'category' => EnumTransactionCategory::MASTERNODE,
                            'status' => EnumTransactionsStatus::SUCCESS,
                            'user_id' => $masternode->user_id,
                            'type' => EnumTransactionType::IN,
                            'amount' => 125,
                            'info' => 'Devolução: ' . $plan->startDateLocal . ' à ' . $plan->endDateLocal,
                        ])->first();

                        if ($transaction) {
                            continue;
                        }

                        $plan->status = EnumMasternodeStatus::CANCELED;
                        $plan->save();

                        $chargeback = Transaction::create([
                            'user_id' => $masternode->user_id,
                            'coin_id' => $masternode->coin_id,
                            'wallet_id' => $wallet->id,
                            'toAddress' => $masternode->payment_address,
                            'amount' => 125,
                            'status' => EnumTransactionsStatus::SUCCESS,
                            'type' => EnumTransactionType::IN,
                            'category' => EnumTransactionCategory::MASTERNODE,
                            'confirmation' => 0,
                            'tax' => 0,
                            'tx' => Uuid::uuid4()->toString(),
                            'info' => 'Devolução: ' . $plan->startDateLocal . ' à ' . $plan->endDateLocal,
                            'error' => '',
                            'market' => '',
                            'price' => '',
                        ]);

                        BalanceService::increments($chargeback);
                    }

                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }

}
