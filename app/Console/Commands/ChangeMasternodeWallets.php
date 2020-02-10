<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\OffScreenController;
use App\Models\Coin;
use App\Models\Masternode;
use App\Models\Transaction;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ChangeMasternodeWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change:lqxwallets';

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
        try {

            $wallets = UserWallet::where([
                'coin_id' => Coin::getByAbbr("LQX")->id,
                'type' => EnumUserWalletType::MASTERNODE
            ])
                ->whereNull('old_address')
                ->get();

            $output = new \Symfony\Component\Console\Output\ConsoleOutput();

            foreach ($wallets as $wallet) {
                $masternode = Masternode::where([
                    'reward_address' => $wallet->address
                ])->first();

                $wallet->old_address = $wallet->address;
                $wallet->address = $masternode->fee_address;
                $wallet->save();

                $output->writeln("<info>-----------------------------</info>");
                $output->writeln("<info>OLD: {$wallet->old_address}</info>");
                $output->writeln("<info>NEW: {$wallet->address}</info>");
            }

        } catch(\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
