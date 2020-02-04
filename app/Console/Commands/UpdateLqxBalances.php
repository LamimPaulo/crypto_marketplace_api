<?php

namespace App\Console\Commands;

use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Admin\Operations\TransactionsController;
use App\Mail\AlertsMail;
use App\Models\Coin;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class UpdateLqxBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:lqxbalance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar , atualizar e bloquear carteiras lqx';

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
                    'type' => EnumUserWalletType::WALLET
                ])
                ->whereIn('coin_id', [
                    Coin::getByAbbr("LQX")->id, Coin::getByAbbr("LQXD")->id
                ])
                ->get();

            foreach ($wallets as $wallet) {

                $transactionController = new TransactionsController(new BalanceService());
                $computed = $transactionController->balanceVerify($wallet->user->email);

                $output->writeln("<info>-----------------------------</info>");
                $output->writeln("<info>{$wallet->user->email}</info>");
                $output->writeln("<info>local balance: {$wallet->balance}</info>");
                $output->writeln("<info>verify balance: {$computed['balances'][$wallet->coin->abbr]['balance_computed']->balance}</info>");
                $output->writeln("<info>verify sum transactions: {$computed['balances'][$wallet->coin->abbr]['balance']}</info>");

                if ($computed['balances'][$wallet->coin->abbr]['balance'] < -0.001) {
                    $user = User::find($wallet->user_id);

                    if (!$user->is_admin) {
                        if (!$user->is_under_analysis) {

                            $user->is_under_analysis = true;
                            $user->save();

                            $user->tokens()->each(function ($token) {
                                $token->delete();
                            });

                            $message = env("APP_NAME") . " - Usuário bloqueado: " . env("ADMIN_URL") . "/user/analysis/" . $wallet->user->email;
                            Mail::to(config('services.devs.me'))
                                ->send(new AlertsMail($message));
                            sleep(2);
                        }
                    }

                    $output->writeln("<info>BLOQUEADO ANALISE</info>");
                }

                if (!$wallet->user->is_admin) {
                    $wallet->balance = $computed['balances'][$wallet->coin->abbr]['balance'];
                    $wallet->save();
                }
            }

        } catch (\Exception $e) {
//            throw new \Exception($e->getLine() . ' - ' . $e->getFile() . ' - '. $e->getMessage());
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }
}
