<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Http\Controllers\OffScreenController;
use App\Models\User\UserWallet;
use Illuminate\Console\Command;

class UpdateOffscreenBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:offscreenbalance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        try {
            $wallets = UserWallet::where([
                'coin_id' => 1,
                'sync' => 0
            ])->get();

            foreach ($wallets as $wallet) {
                $result = OffScreenController::post(EnumOperationType::IMPORT_ADDRESS, ['address' => $wallet->address, 'amount' => $wallet->balance], 'BTC');

                if (isset($result['wallet']) AND $result['wallet'] == $wallet->address) {
                    $wallet->sync = true;
                    $wallet->save();
                }
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
