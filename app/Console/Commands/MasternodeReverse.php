<?php

namespace App\Console\Commands;

use App\Enum\EnumMasternodeStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Models\Masternode;
use App\Models\Transaction;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class MasternodeReverse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masternode:reverse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Estorno de Masternodes Bugados';

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
        if (env("APP_ENV") == 'local') {
            $this->reverseTransactions();
        }
    }

    private function reverseTransactions()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        try {
            DB::beginTransaction();

            $bugados = DB::table('masternodes_bugados')->get();

            foreach ($bugados as $bugado) {
                $masternode = Masternode::where('payment_address', $bugado->address)->first();

                if ($masternode) {
                    $masternode->label = 'Devolver Pagamento';
                    $masternode->status = EnumMasternodeStatus::CANCELED;
                    $masternode->save();

                    $wallet = UserWallet::where([
                        'user_id' => $masternode->user_id,
                        'coin_id' => $masternode->coin_id,
                        'type' => EnumUserWalletType::WALLET,
                    ])->first();

                    $amount = 1000;

                    //CHECK PAYMENT AMOUNT
                    $transaction = Transaction::where([
                        'toAddress' => $masternode->payment_address,
                        'category' => EnumTransactionCategory::TRANSACTION,
                        'user_id' => $masternode->user_id,
                        'type' => EnumTransactionType::OUT,
                    ])->orderByDesc('amount')->first();

                    if ($transaction) {
                        $amount = $transaction->amount;
                    }

                    //CHECK CHARGEBACK TRANSACTION EXISTS
                    $chargeback = Transaction::where([
                        'toAddress' => $masternode->payment_address,
                        'category' => EnumTransactionCategory::MASTERNODE_UNDO,
                        'user_id' => $masternode->user_id,
                        'wallet_id' => $wallet->id,
                    ])->first();

                    if (!$chargeback) {
                        $chargeback = Transaction::create([
                            'user_id' => $masternode->user_id,
                            'coin_id' => $masternode->coin_id,
                            'wallet_id' => $wallet->id,
                            'toAddress' => $masternode->payment_address,
                            'amount' => $amount,
                            'status' => EnumTransactionsStatus::SUCCESS,
                            'type' => EnumTransactionType::IN,
                            'category' => EnumTransactionCategory::MASTERNODE_UNDO,
                            'confirmation' => 0,
                            'tax' => 0,
                            'tx' => Uuid::uuid4()->toString(),
                            'info' => 'Devolução de Pagamento de Masternode',
                            'error' => '',
                            'market' => '',
                            'price' => '',
                        ]);

                        BalanceService::increments($chargeback);
                    }

                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }

}
