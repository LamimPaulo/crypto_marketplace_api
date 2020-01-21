<?php

namespace App\Console\Commands;

use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Admin\Operations\TransactionsController;
use App\Models\Coin;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Console\Command;

class UpdateBtcBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:btcbalance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar , atualizar e bloquear carteiras btc';

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
            $wallets = UserWallet::with(['user', 'coin'])
                ->where([
                    'coin_id' => Coin::getByAbbr("BRL")->id,
                    'type' => EnumUserWalletType::WALLET
                ])
                ->get();

            $output->writeln("<info>START</info>");

            foreach ($wallets as $wallet) {

                $transactionController = new TransactionsController(new BalanceService());
                $computed = $transactionController->balanceVerify($wallet->user->email);

                if ($computed['balances']['BRL']['balance'] >= 50) {
                    $output->writeln("<info>{$wallet->user->email}</info>");
                }
            }

            $output->writeln("<info>END</info>");

        } catch
        (\Exception $e) {
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }
}
