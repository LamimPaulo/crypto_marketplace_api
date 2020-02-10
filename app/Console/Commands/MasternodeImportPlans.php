<?php

namespace App\Console\Commands;

use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumTransactionCategory;
use App\Models\MasternodeImport;
use App\Models\MasternodeUserPlan;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MasternodeImportPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masternode:importplans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Masternodes Plans';

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
            $this->importPlans();
        }
    }

    private function importPlans()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        try {
            DB::beginTransaction();

            $importedPlans = MasternodeImport::with('masternode')
                ->where([
                    'is_sync' => false,
                ])
                ->get();

            foreach ($importedPlans as $importedPlan) {

                $output->writeln("<info>_____________________________</info>");
                $output->writeln("<info>{$importedPlan->masternode->user->email}</info>");

                $start = $importedPlan->masternode->created_at;
                $end = $importedPlan->masternode->created_at->addMonth()->subDay();

                $rewards = Transaction::where([
                    'toAddress' => $importedPlan->masternode->fee_address,
                    'category' => EnumTransactionCategory::MASTERNODE_REWARD,
                    'user_id' => $importedPlan->masternode->user->id
                ])
                    ->orderBy('created_at')
                    ->get();

                if (count($rewards)) {
                    $importedPlan->is_rewarded = true;

                    $firstReward = $rewards->first();
                    $lastReward = $rewards->last();

                    $start = $firstReward->created_at;
                    $end = $lastReward->created_at->addMonth()->subDay();

                    $output->writeln("<info>**REWARDED</info>");
                }

                for ($m = 1; $m <= $importedPlan->months; $m++) {
                    $masternodePlan = MasternodeUserPlan::create([
                        'user_id' => $importedPlan->masternode->user->id,
                        'masternode_plan_id' => 1,
                        'masternode_id' => $importedPlan->masternode->id,
                        'start_date' => $start,
                        'end_date' => $end,
                        'status' => EnumMasternodeStatus::SUCCESS
                    ]);

                    $output->writeln("<info>{$masternodePlan->start_date}</info>");
                    $output->writeln("<info>{$masternodePlan->end_date}</info>");

                    $start = Carbon::parse($end)->addDay();
                    $end = Carbon::parse($end)->addMonth();
                }

                $importedPlan->is_sync = true;
                $importedPlan->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }

}
