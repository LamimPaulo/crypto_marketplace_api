<?php

namespace App\Console\Commands;

use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Models\Masternode;
use App\Models\MasternodeUserPlan;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MasternodeBlockRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masternode:blockrewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Masternodes Plans';

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
            //PEGAR TODAS AS RECOMPENSAS E MUDAR O STATUS PRA BLOQUEADA
            $this->updateRewards();
            //MUDAR O STATUS DO MASTERNODE PARA PENDENTE DE PAGAMENTO
            //$this->updateMasternode();
        }
    }

    private function updateRewards()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        try {
            DB::beginTransaction();

            $transactions = Transaction::where([
                'status' => EnumTransactionsStatus::SUCCESS,
                'category' => EnumTransactionCategory::MASTERNODE_REWARD
            ])->get();

            foreach ($transactions as $transaction) {
                $transaction->status = EnumTransactionsStatus::BLOCKED;
                $transaction->save();

                $output->writeln("<info>------------------</info>");
                $output->writeln("<info>{$transaction->tx}</info>");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }

    private function updateMasternode()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        try {
            DB::beginTransaction();

            $masternodes = Masternode::where([
                'status' => EnumMasternodeStatus::SUCCESS,
            ])
                ->whereNotNull('privkey')
                ->get();

            foreach ($masternodes as $masternode) {
                $output->writeln("<info>--------------------------</info>");
                $output->writeln("<info>{$masternode->fee_address}</info>");

                $masternode->status = EnumMasternodeStatus::PENDING_PAYMENT;
                $masternode->save();

                $transactions = Transaction::where([
                    'status' => EnumTransactionsStatus::BLOCKED,
                    'category' => EnumTransactionCategory::MASTERNODE_REWARD,
                    'toAddress' => $masternode->fee_address,
                    'user_id' => $masternode->user_id,
                ])->orderBy('created_at')->get();

                if(!count($transactions)){
                    continue;
                }

                $firstTransaction = $transactions->first();
                $lastTransaction = $transactions->last();

                $output->writeln("<info>FIRST</info>");
                $output->writeln("<info>{$firstTransaction->created_at}</info>");
                $output->writeln("<info>LAST</info>");
                $output->writeln("<info>{$lastTransaction->created_at}</info>");
                $output->writeln("<info>-----------------</info>");

                $diff_in_months = $lastTransaction->created_at->diffInMonths($firstTransaction->created_at);

                $output->writeln("<info>DIFF: {$diff_in_months}</info>");
                $output->writeln("<info>-----------------</info>");

                $start = $firstTransaction->created_at;
                $end = $firstTransaction->created_at->addMonth()->subDay();

                for ($m = 1; $m <= $diff_in_months; $m++) {
                    $planData = [
                        'user_id' => $firstTransaction->user_id,
                        'masternode_plan_id' => 1,
                        'masternode_id' => $masternode->id,
                        'start_date' => $start,
                        'end_date' => $end,
                    ];

                    $masternodePlan = MasternodeUserPlan::where($planData)->first();

                    if (!$masternodePlan) {
                        $planData['status'] = EnumMasternodeStatus::PENDING_PAYMENT;
                        $masternodePlan = MasternodeUserPlan::create($planData);
                    }

                    $output->writeln("<info>{$masternodePlan->start_date}</info>");
                    $output->writeln("<info>{$masternodePlan->end_date}</info>");

                    $start = Carbon::parse($end)->addDay();
                    $end = Carbon::parse($end)->addMonth();
                }

                //VERIFICAR SE EXISTEM RECOMPENSAS ALÉM DO MES FECHADO
                if ($lastTransaction->created_at->gt($end)) {
                    $planData = [
                        'user_id' => $firstTransaction->user_id,
                        'masternode_plan_id' => 1,
                        'masternode_id' => $masternode->id,
                        'start_date' => $start,
                        'end_date' => $end,
                    ];

                    $masternodePlan = MasternodeUserPlan::where($planData)->first();

                    if (!$masternodePlan) {
                        $planData['status'] = EnumMasternodeStatus::PENDING_PAYMENT;
                        $masternodePlan = MasternodeUserPlan::create($planData);
                    }

                    $output->writeln("<info>{$masternodePlan->start_date}</info>");
                    $output->writeln("<info>{$masternodePlan->end_date}</info>");
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
