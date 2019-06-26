<?php

namespace App\Console\Commands;

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
            $wallets = UserWallet::where('sync', 0)
                ->where('coin_id', 1)
                ->with(['user', 'coin'])->get();

            foreach ($wallets as $wallet) {
                $client = new \GuzzleHttp\Client();

                $url = str_replace("operation", "syncwallet", config("services.offscreen.{$wallet->coin->abbr}"));

                $response = $client->post($url, [
                    \GuzzleHttp\RequestOptions::JSON => [
                        "amount" => $wallet->balance,
                        "address" => $wallet->address,
                        "key" => env("ADM_KEY")
                    ]
                ]);

                $statusCode = $response->getStatusCode();
                if ($statusCode != 200) {
                    throw new \Exception("Erro na syncronização. [{$wallet->address}] [$statusCode]");
                }

                $wallet->sync = true;
                $wallet->save();

                $wallet->user->is_under_analysis = false;
                $wallet->save();
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }


//        try {
//            $wallets = UserWallet::where([
//                'coin_id' => 1,
//                'sync' => 0
//            ])->get();
//
//            foreach ($wallets as $wallet) {
//                $result = OffScreenController::post(EnumOperationType::IMPORT_ADDRESS, ['address' => $wallet->address, 'amount' => $wallet->balance], 'BTC');
//
//                if ($result != 200) {
//                    throw new \Exception("Erro na syncronização. [{$wallet->address}]");
//                }
//                $wallet->sync = true;
//                $wallet->save();
//            }
//
//        } catch (\Exception $e) {
//            throw new \Exception($e->getMessage());
//        }
    }
}
