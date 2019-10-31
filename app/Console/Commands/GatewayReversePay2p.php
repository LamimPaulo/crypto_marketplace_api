<?php

namespace App\Console\Commands;

use App\Enum\EnumGatewayCategory;
use App\Enum\EnumGatewayStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Models\Coin;
use App\Models\Gateway;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class GatewayReversePay2p extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway:reversepay2p';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Devoler Pagamentos do Gateway feitos para Pay2p';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $lqx = Coin::getByAbbr("LQX");
            $gateway = Gateway::where([
                'coin_id' => $lqx->id,
                'category' => EnumGatewayCategory::PAY2P,
            ])
                ->whereNull('txid_reverse')
                ->get();

            foreach ($gateway as $g) {
                $transaction = Transaction::with('user')->where('toAddress', $g->address)->first();

                if ($transaction) {

                    $wallet = UserWallet::where([
                        'type' => EnumUserWalletType::WALLET,
                        'coin_id' => $lqx->id,
                        'user_id' => $transaction->user_id
                    ])->first();

                    if ($wallet) {


                        DB::beginTransaction();

                        $internalTx = Uuid::uuid4()->toString();
                        $newTransaction = Transaction::create([
                            'user_id' => $transaction->user_id,
                            'sender_user_id' => $transaction->user_id,
                            'coin_id' => $lqx->id,
                            'wallet_id' => $wallet->id,
                            'toAddress' => $wallet->address,
                            'amount' => $g->received,
                            'status' => EnumTransactionsStatus::SUCCESS,
                            'type' => EnumTransactionType::IN,
                            'category' => EnumTransactionCategory::LQX_REVERSION,
                            'fee' => 0,
                            'taxas' => 0,
                            'tx' => $internalTx,
                            'info' => "Devolução de Pagamento no Gateway.",
                            'error' => '',
                            'is_internal' => true,
                        ]);

                        TransactionStatus::create([
                            'status' => $newTransaction->status,
                            'transaction_id' => $newTransaction->id,
                        ]);

                        (new BalanceService)->increments($newTransaction);

                        $g->info = "Pagamento devolvido para o Usuário. Verificar transação pela txid de estorno.";
                        $g->txid_reverse = $internalTx;
                        $g->status = EnumGatewayStatus::REVERSED;
                        $g->save();

                        DB::commit();
                    }
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

}
