<?php

namespace App\Console\Commands;

use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\MasternodeController;
use App\Models\Coin;
use App\Models\Masternode;
use App\Models\MasternodeUserPlan;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MasternodeGeneratePlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masternode:generateplans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Masternodes Plans';

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
            DB::beginTransaction();
            $output->writeln("<info>START</info>");
            $output->writeln("<info>--------------------------</info>");

            $transactions = Transaction::with('masternode')
                ->where([
                    'status' => EnumTransactionsStatus::BLOCKED,
                    'category' => EnumTransactionCategory::MASTERNODE_REWARD,
                ])
                ->orderBy('created_at')
                ->get();

            foreach ($transactions as $transaction) {
                $output->writeln("<info>{$transaction->toAddress}</info>");
                $output->writeln("<info>{$transaction->masternode->fee_address}</info>");

                $masternodePlan = MasternodeUserPlan::where([
                    'user_id' => $transaction->user_id,
                    'masternode_id' => $transaction->masternode->id,
                ])
                    ->where('start_date', '<=', $transaction->created_at)
                    ->where('end_date', '>=', $transaction->created_at)
                    ->first();

                if (!$masternodePlan) {
                    $start = $transaction->created_at;
                    $end = $transaction->created_at->addMonth()->subDay();

                    //Verificar ultimo plano deste masternode e pegar a data final
                    $lastPlan = MasternodeUserPlan::where([
                        'user_id' => $transaction->user_id,
                        'masternode_id' => $transaction->masternode->id,
                    ])->orderByDesc('end_date')->first();

                    if ($lastPlan) {
                        $start = Carbon::parse($lastPlan->end_date)->addDay();
                        $end = Carbon::parse($lastPlan->end_date)->addMonth();
                    }

                    $masternodePlan = MasternodeUserPlan::create([
                        'user_id' => $transaction->user_id,
                        'masternode_plan_id' => 1,
                        'masternode_id' => $transaction->masternode->id,
                        'start_date' => $start,
                        'end_date' => $end,
                        'status' => EnumMasternodeStatus::PENDING_PAYMENT
                    ]);

                    $transaction->masternode->status = EnumMasternodeStatus::PENDING_PAYMENT;
                    $transaction->masternode->save();

                } else {
                    //verificar se plano esta pago para recuperar recompensas automaticamente
                    if ($masternodePlan->status == EnumMasternodeStatus::SUCCESS) {
                        $from = UserWallet::where([
                            'user_id' => $masternodePlan->user_id,
                            'coin_id' => Coin::getByAbbr("LQX")->id,
                            'type' => EnumUserWalletType::WALLET
                        ])->first();

                        $this->contabilizeRewards($masternodePlan, $from);

                        $transaction->masternode->status = EnumMasternodeStatus::SUCCESS;
                        $transaction->masternode->save();
                    }

                    if ($masternodePlan->status == EnumMasternodeStatus::PENDING_PAYMENT) {
                        $transaction->masternode->status = EnumMasternodeStatus::PENDING_PAYMENT;
                        $transaction->masternode->save();
                    }

                    if ($masternodePlan->status == EnumMasternodeStatus::REFUSED) {
                        $this->refuseRewards($masternodePlan);

                        $transaction->masternode->status = EnumMasternodeStatus::CANCELED;
                        $transaction->masternode->save();
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

    private function refuseRewards($plan)
    {
        $rewards = Transaction::where([
            'status' => EnumTransactionsStatus::BLOCKED,
            'category' => EnumTransactionCategory::MASTERNODE_REWARD,
            'toAddress' => $plan->masternode->fee_address,
            'user_id' => $plan->user_id,
        ])->whereBetween('created_at', [
            $plan->start_date->format("Y-m-d 00:00:00"),
            $plan->end_date->format("Y-m-d 23:59:59")
        ])->get();

        foreach ($rewards as $reward) {
            $reward->status = EnumTransactionsStatus::REFUSED;
            $reward->save();

            TransactionStatus::create([
                'status' => $reward->status,
                'transaction_id' => $reward->id,
            ]);
        }

        $plan->masternode->user_id = env("LIQUIDEX_USER");
        $plan->masternode->save();
    }

    private function contabilizeRewards($plan, $wallet)
    {
        $rewards = Transaction::where([
            'status' => EnumTransactionsStatus::BLOCKED,
            'category' => EnumTransactionCategory::MASTERNODE_REWARD,
            'toAddress' => $plan->masternode->fee_address,
            'user_id' => $plan->user_id,
        ])->whereBetween('created_at', [
            $plan->start_date->format("Y-m-d 00:00:00"),
            $plan->end_date->format("Y-m-d 23:59:59")
        ])->get();

        foreach ($rewards as $reward) {
            $reward->status = EnumTransactionsStatus::SUCCESS;
            $reward->save();

            TransactionStatus::create([
                'status' => $reward->status,
                'transaction_id' => $reward->id,
            ]);

            $reward->wallet_id = $wallet->id;
            BalanceService::increments($reward);
        }
    }
}
