<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\OffScreenController;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class SyncLqxWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:lqxwallets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and Sync Balance for LQX Wallets';

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
            $users = User::all();
            $lqxd = Coin::getByAbbr("LQXD");

            foreach ($users as $user) {
                $wallet = UserWallet::where([
                    "user_id" => $user->id,
                    "coin_id" => $lqxd->id
                ])->first();

                if (!$wallet) {
                    $address = Uuid::uuid4()->toString();
                    $output->writeln("<info>address: {$address}</info>");

                    UserWallet::create([
                        'user_id' => $user->id,
                        'coin_id' => $lqxd->id,
                        'address' => $address,
                        'balance' => 0
                    ]);
                }
            }

        } catch (\Exception $e) {
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }
}
