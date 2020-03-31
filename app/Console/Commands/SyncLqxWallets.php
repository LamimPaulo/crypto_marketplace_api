<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
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
    protected $description = 'Create new address and Sync LQX Wallets';

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
            $coin = Coin::getByAbbr('LQX');
            $wallets = UserWallet::where([
                'sync' => false,
                'type' => EnumUserWalletType::WALLET,
                'coin_id' => $coin->id
            ])->get();

            $output->writeln("<info>START</info>");

            foreach ($wallets as $wallet) {
                $wallet->old_address = $wallet->address;
                $wallet->address = OffScreenController::post(EnumOperationType::CREATE_ADDRESS, [], 'LQX');
                $wallet->sync = true;
                $wallet->save();
                $output->writeln("<info>SAVED={$wallet->address}</info>");
            }

            $output->writeln("<info>FINISHED</info>");
        } catch (\Exception $e) {
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }
}
