<?php

namespace App\Console\Commands;

use App\Http\Controllers\OffScreenController;
use App\Models\Coin;
use Illuminate\Console\Command;

class estimateFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'estimate:fee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Estimate Smart Fee';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (env("APP_ENV") !== 'local') {
            $this->estimateFeeBTC();
        }
    }

    public function estimateFeeBTC()
    {
        try {
            $coins = Coin::where([
                'is_crypto' => true,
                'is_wallet' => true,
                'is_active' => true
            ])
                ->where('id', '<>', Coin::getByAbbr('LQXD')->id)->get();

            foreach ($coins as $coin) {
                $fee_1 = OffScreenController::post(\App\Enum\EnumOperationType::ESTIMATE_SMART_FEE, 1, $coin->abbr);
                $fee_3 = OffScreenController::post(\App\Enum\EnumOperationType::ESTIMATE_SMART_FEE, 3, $coin->abbr);
                $fee_6 = OffScreenController::post(\App\Enum\EnumOperationType::ESTIMATE_SMART_FEE, 6, $coin->abbr);
                $coin->fee_low = is_numeric($fee_6) AND $fee_6 > 0 ? sprintf("%.8f", $fee_6) : 0.00001;
                $coin->fee_avg = is_numeric($fee_3) AND $fee_3 > 0 ? sprintf("%.8f", $fee_3) : 0.00001;
                $coin->fee_high = is_numeric($fee_1) AND $fee_1 > 0  ? sprintf("%.8f", $fee_1) : 0.00001;
                $coin->save();
            }

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
