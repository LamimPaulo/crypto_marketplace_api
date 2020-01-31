<?php

namespace App\Console\Commands;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Models\Transaction;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class LqxWithdrawals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lqx:withdrawals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Withdrawal Balance for LQX Wallets';

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

            $operations = $this->operations();

            foreach ($operations as $operation) {

                $output->writeln("<info>-----------------------------</info>");
                $output->writeln("<info>{$operation['email']}</info>");
                $output->writeln("<info>LQXD: {$operation['amount']}</info>");


                $wallet = UserWallet::with('coin')
                    ->whereHas('coin', function ($coin) {
                        return $coin->where('abbr', 'LIKE', 'LQXD');
                    })
                    ->whereHas('user', function ($user) use ($operation) {
                        return $user->where('email', 'LIKE', $operation['email']);
                    })
                    ->first();

                $tx = Uuid::uuid4()->toString();

                $transaction_in = Transaction::create([
                    'user_id' => $wallet->user_id,
                    'coin_id' => $wallet->coin_id,
                    'wallet_id' => $wallet->id,
                    'toAddress' => $wallet->address,
                    'amount' => $operation['amount'],
                    'status' => EnumTransactionsStatus::SUCCESS,
                    'type' => EnumTransactionType::IN,
                    'category' => EnumTransactionCategory::CREDMINER,
                    'fee' => 0,
                    'tax' => 0,
                    'tx' => $tx,
                    'info' => '**Correção da Migração de Contas',
                    'error' => '',
                    'is_internal' => false,
                ]);

                $transaction_in->created_at = "2020-01-28 18:00:00";

                BalanceService::increments($transaction_in);

            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function operations()
    {
        return [
            [
                "email" => "alisson_carpino@hotmail.com",
                "amount" => "291.48"
            ],
            [
                "email" => "alissonmitsu@gmail.com",
                "amount" => "134.02"
            ],
            [
                "email" => "alveri48239@outlook.com",
                "amount" => "236.24"
            ],
            [
                "email" => "anaguiocor@gmail.com",
                "amount" => "168.42"
            ],
            [
                "email" => "aquilamourao@gmail.com",
                "amount" => "1259.34"
            ],
            [
                "email" => "aquilamourao@gmail.com",
                "amount" => "112.74"
            ],
            [
                "email" => "art221980@hotmail.com",
                "amount" => "1010.9"
            ],
            [
                "email" => "barbaracrduarte@gmail.com",
                "amount" => "514.4"
            ],
            [
                "email" => "brandes.engenhariadepesca@gmail.com",
                "amount" => "1182.02"
            ],
            [
                "email" => "brayres@gmail.com",
                "amount" => "2400.8"
            ],
            [
                "email" => "brayres@gmail.com",
                "amount" => "1862"
            ],
            [
                "email" => "brayres@gmail.com",
                "amount" => "1862"
            ],
            [
                "email" => "bruno.pamphile@gmail.com",
                "amount" => "804.4"
            ],
            [
                "email" => "bruno.pamphile@gmail.com",
                "amount" => "2806.62"
            ],
            [
                "email" => "cacmemag@hotmail.com",
                "amount" => "261.84"
            ],
            [
                "email" => "cicero_osvaldo@hotmail.com",
                "amount" => "571.4"
            ],
            [
                "email" => "cicero_osvaldo@hotmail.com",
                "amount" => "2768.74"
            ],
            [
                "email" => "cobachuka@gmail.com",
                "amount" => "2230.16"
            ],
            [
                "email" => "comissariopc@gmail.com",
                "amount" => "3083.74"
            ],
            [
                "email" => "conta.isa1956@gmail.com",
                "amount" => "7417.68"
            ],
            [
                "email" => "credmelza1@gmail.com",
                "amount" => "4888"
            ],
            [
                "email" => "crvg.louzada@gmail.com",
                "amount" => "2501.54"
            ],
            [
                "email" => "crvg.louzada@gmail.com",
                "amount" => "1862"
            ],
            [
                "email" => "crvg.louzada@gmail.com",
                "amount" => "1862"
            ],
            [
                "email" => "deboraribeiro_poa@yahoo.com.br",
                "amount" => "7232.24"
            ],
            [
                "email" => "dogoncal@bol.com.br",
                "amount" => "2156"
            ],
            [
                "email" => "dogoncal@bol.com.br",
                "amount" => "2376.8"
            ],
            [
                "email" => "dorismiya@gmail.com",
                "amount" => "4024.54"
            ],
            [
                "email" => "eduardohidraor1981@outlook.com",
                "amount" => "753.68"
            ],
            [
                "email" => "edzuar@gmail.com",
                "amount" => "3385.6"
            ],
            [
                "email" => "eliduarte2@hotmail.com",
                "amount" => "71.66"
            ],
            [
                "email" => "elisondasilva@outlook.com",
                "amount" => "5482.46"
            ],
            [
                "email" => "elisonwild@gmail.com",
                "amount" => "2012.1"
            ],
            [
                "email" => "endryo@outlook.com",
                "amount" => "3440.86"
            ],
            [
                "email" => "fabianocravoscredminer@gmail.com",
                "amount" => "119.2"
            ],
            [
                "email" => "fabianocravoscredminer@gmail.com",
                "amount" => "596.16"
            ],
            [
                "email" => "fabianocravoscredminer@gmail.com",
                "amount" => "3501.1"
            ],
            [
                "email" => "felipe_jose_silva@hotmail.com",
                "amount" => "164.8"
            ],
            [
                "email" => "fernandosausensausen2016@gmail.com",
                "amount" => "4404.4"
            ],
            [
                "email" => "fernandosausensausen2016@gmail.com",
                "amount" => "3384.68"
            ],
            [
                "email" => "flaubert.bonfim@gmail.com",
                "amount" => "4725.48"
            ],
            [
                "email" => "fmatuella@hotmail.com",
                "amount" => "1085.8"
            ],
            [
                "email" => "fmatuella@hotmail.com",
                "amount" => "1643.56"
            ],
            [
                "email" => "fmatuella@hotmail.com",
                "amount" => "2171.6"
            ],
            [
                "email" => "frb.costa@gmail.com",
                "amount" => "390.2"
            ],
            [
                "email" => "gabrielamuraro0@gmail.com",
                "amount" => "5928.06"
            ],
            [
                "email" => "gabrielamuraro0@gmail.com",
                "amount" => "7685.08"
            ],
            [
                "email" => "gabrielamuraro0@gmail.com",
                "amount" => "890.64"
            ],
            [
                "email" => "gabrielocunha2019@gmail.com",
                "amount" => "270.96"
            ],
            [
                "email" => "geisonduarte@zipmail.com.br",
                "amount" => "173.62"
            ],
            [
                "email" => "gilsonyoshizawa@gmail.com",
                "amount" => "296.32"
            ],
            [
                "email" => "glauciapinheiro10@gmail.com",
                "amount" => "319.5"
            ],
            [
                "email" => "gregsartori@incomil.com.br",
                "amount" => "234.02"
            ],
            [
                "email" => "isabelaaphm@yahoo.com.br",
                "amount" => "1274.5"
            ],
            [
                "email" => "isabelaaphm@yahoo.com.br",
                "amount" => "118.32"
            ],
            [
                "email" => "isackfa@gmail.com",
                "amount" => "540.1"
            ],
            [
                "email" => "jessicazanoni1991@gmail.com",
                "amount" => "1661.1"
            ],
            [
                "email" => "jopesil99@hotmail.com",
                "amount" => "386.74"
            ],
            [
                "email" => "jrf.abel@outlook.com",
                "amount" => "655.54"
            ],
            [
                "email" => "jrf.abel@outlook.com",
                "amount" => "1990"
            ],
            [
                "email" => "juansilveira@gmail.com",
                "amount" => "1641.14"
            ],
            [
                "email" => "juniormesquitacunha@gmail.com",
                "amount" => "19045.82"
            ],
            [
                "email" => "kerciaqueiroz@gmail.com",
                "amount" => "2551.5"
            ],
            [
                "email" => "kerciaqueiroz@gmail.com",
                "amount" => "4801.96"
            ],
            [
                "email" => "lhsn2010@hotmail.com",
                "amount" => "142.2"
            ],
            [
                "email" => "lucas19101910@gmail.com",
                "amount" => "214.56"
            ],
            [
                "email" => "lucas19101910@gmail.com",
                "amount" => "135.2"
            ],
            [
                "email" => "luisascolaricorrea@gmail.com",
                "amount" => "6116.94"
            ],
            [
                "email" => "luizdomingues21@hotmail.com",
                "amount" => "530.26"
            ],
            [
                "email" => "marciliomorais1987@gmail.com",
                "amount" => "222.6"
            ],
            [
                "email" => "marianorocha@gmail.com",
                "amount" => "4495.14"
            ],
            [
                "email" => "max.bs.eptc@gmail.com",
                "amount" => "3615.54"
            ],
            [
                "email" => "max.bs.eptc@gmail.com",
                "amount" => "2959.16"
            ],
            [
                "email" => "max.bs.eptc@gmail.com",
                "amount" => "3779.22"
            ],
            [
                "email" => "maxdoiapi@hotmail.com",
                "amount" => "44.06"
            ],
            [
                "email" => "mgui_almeida@hotmail.com",
                "amount" => "1516.8"
            ],
            [
                "email" => "michel-locatelli@outlook.com",
                "amount" => "1385.66"
            ],
            [
                "email" => "nataliedoubleyes@gmail.com",
                "amount" => "2405.06"
            ],
            [
                "email" => "nataliedoubleyes@gmail.com",
                "amount" => "988.96"
            ],
            [
                "email" => "nildopereira7784@gmail.com",
                "amount" => "472.44"
            ],
            [
                "email" => "nildopereira7784@gmail.com",
                "amount" => "2513.6"
            ],
            [
                "email" => "ola@keldesigns.com.br",
                "amount" => "337.26"
            ],
            [
                "email" => "Piovesan.luan92@gmail.com",
                "amount" => "628.44"
            ],
            [
                "email" => "psaazevedo3010@gmail.com",
                "amount" => "1198"
            ],
            [
                "email" => "rabelcoach@gmail.com",
                "amount" => "3377.76"
            ],
            [
                "email" => "rafaeldemontier@hotmail.com",
                "amount" => "194.34"
            ],
            [
                "email" => "reynaldoalves7@gmail.com",
                "amount" => "175.82"
            ],
            [
                "email" => "rojaimesilva@yahoo.com.br",
                "amount" => "711.54"
            ],
            [
                "email" => "savionascimento1968@gmail.com",
                "amount" => "1256.64"
            ],
            [
                "email" => "sdulissesjr@gmail.com",
                "amount" => "66.22"
            ],
            [
                "email" => "sr.wilsonps@gmail.com",
                "amount" => "2657.3"
            ],
            [
                "email" => "thiagogdn@hotmail.com",
                "amount" => "94.6"
            ],
            [
                "email" => "troncoso.andre@yahoo.com.br",
                "amount" => "7.52"
            ],
            [
                "email" => "viniciuspimp@hotmail.com",
                "amount" => "4058.26"
            ],
            [
                "email" => "wellingtongarcia2019@outlook.com",
                "amount" => "1801.92"
            ],
            [
                "email" => "zefurlan.lqx@gmail.com",
                "amount" => "96.36"
            ],
        ];
    }
}
