<?php

namespace App\Console\Commands;

use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Admin\Operations\TransactionsController;
use App\Mail\AlertsMail;
use App\Models\Coin;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
                    'coin_id' => Coin::getByAbbr("BTC")->id,
                    'type' => EnumUserWalletType::WALLET
                ])
                ->where('balance', '>=', 0.00001)
                ->get();

            foreach ($wallets as $wallet) {

                $transactionController = new TransactionsController(new BalanceService());
                $computed = $transactionController->balanceVerify($wallet->user->email);

                $output->writeln("<info>-----------------------------</info>");
                $output->writeln("<info>{$wallet->user->email}</info>");
                $output->writeln("<info>local balance: {$wallet->balance}</info>");
                $output->writeln("<info>verify balance: {$computed['balances']['BTC']['balance_computed']->balance}</info>");
                $output->writeln("<info>verify sum transactions: {$computed['balances']['BTC']['balance']}</info>");

                if ($computed['balances']['BTC']['balance'] < 0) {
                    $wallet->user->is_under_analysis = true;
                    $wallet->user->tokens()->each(function ($token) {
                        $token->delete();
                    });

                    $message = env("APP_NAME"). " - Usuário bloqueado: " . $wallet->user->email;
                    Mail::to(env('DEV_MAIL', 'cristianovelkan@gmail.com'))->send(new AlertsMail($message));

                    $output->writeln("<info>BLOQUEADO ANALISE</info>");
                }

                $wallet->balance = $computed['balances']['BTC']['balance'];
                $wallet->save();
            }

        } catch (\Exception $e) {
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }
}
