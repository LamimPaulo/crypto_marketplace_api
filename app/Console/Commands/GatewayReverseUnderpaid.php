<?php

namespace App\Console\Commands;

use App\Enum\EnumGatewayCategory;
use App\Enum\EnumGatewayStatus;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Admin\Operations\GatewayController;
use App\Models\Coin;
use App\Models\Gateway;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class GatewayReverseUnderpaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway:reverseunderpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Devoler Pagamentos do Gateway pagos abaixo para carteiras internas da Liquidex';

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
            $gateway = Gateway::where('coin_id', $lqx->id)
                ->whereDate('created_at', '>', '2019-11-02')
                ->where('amount', '>', 'received')
                ->whereNull('txid_reverse')
                ->whereIn('status', [EnumGatewayStatus::UNDERPAID, EnumGatewayStatus::UNDERPAIDEXPIRED])
                ->whereIn('category', [EnumGatewayCategory::PAY2P, EnumGatewayCategory::CREDMINER])
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

                        if (($g->received - $lqx->fee_avg) > 0) {

                            DB::beginTransaction();

                            $internalTx = Uuid::uuid4()->toString();
                            $newTransaction = Transaction::create([
                                'user_id' => $transaction->user_id,
                                'sender_user_id' => $transaction->user_id,
                                'coin_id' => $lqx->id,
                                'wallet_id' => $wallet->id,
                                'toAddress' => $wallet->address,
                                'amount' => sprintf("%.8f", ($g->received - $lqx->fee_avg)),
                                'status' => EnumTransactionsStatus::SUCCESS,
                                'type' => EnumTransactionType::IN,
                                'category' => EnumTransactionCategory::LQX_REVERSION,
                                'fee' => $lqx->fee_avg,
                                'taxas' => 0,
                                'tx' => $internalTx,
                                'info' => "Estorno de Pagamento Abaixo do esperado no Gateway. (txid: $g->txid)",
                                'error' => '',
                                'is_internal' => true,
                            ]);

                            TransactionStatus::create([
                                'status' => $newTransaction->status,
                                'transaction_id' => $newTransaction->id,
                            ]);

                            (new BalanceService)->increments($newTransaction);

                            $g->info = "Pagamento abaixo devolvido para o Usuário. Verificar transação pela txid de estorno. ($internalTx)";
                            $g->txid_reverse = $internalTx;
                            $g->save();

                            DB::commit();
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

}
