<?php

namespace App\Console\Commands;

use App\Enum\EnumMasternodeStatus;
use App\Models\Masternode;
use App\Models\MasternodeUserPlan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MasternodeVerifyPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masternode:verifypayments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Masternodes Montly Payments';

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

            $masternodes = Masternode::where([
                'status' => EnumMasternodeStatus::PENDING_PAYMENT,
            ])
                ->whereNotNull('privkey')
                ->get();

            foreach ($masternodes as $masternode) {
                $output->writeln("<info>--------------------------</info>");
                $output->writeln("<info>{$masternode->fee_address}</info>");

                $masternodePlan = MasternodeUserPlan::where([
                    'user_id' => $masternode->user_id,
                    'masternode_id' => $masternode->id,
                    'status' => EnumMasternodeStatus::SUCCESS
                ])
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now())
                    ->exists();

                if ($masternodePlan) {
                    $masternode->status = EnumMasternodeStatus::SUCCESS;
                    $masternode->save();
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
