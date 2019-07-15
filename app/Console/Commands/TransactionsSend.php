<?php

namespace App\Console\Commands;

use App\Enum\EnumOperationType;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumUserLevelLimitType;
use App\Http\Controllers\OffScreenController;
use App\Mail\UnderAnalysisMail;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\User\UserLevelLimit;
use App\Models\User\UserWallet;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TransactionsSend extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia transações pendentes para o core correspondente';

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

        $pendingTransaction = Transaction::listPending();
        foreach ($pendingTransaction as $pending) {
            $this->BTC($pending);
        }

        $authorizedTransaction = Transaction::listAuthorized();
        foreach ($authorizedTransaction as $authorized) {
            $this->sendBTC($authorized);
        }
    }

    /**
     * Execute the console command.
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function connectionSendBTC($id)
    {
        try {
            $pending = Transaction::listUnique($id);
            return $this->sendBTC($pending);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Envia transações baseadas em BitCoin
     * @param type $pending
     * @return bool
     * @throws \Exception
     */
    private function BTC($pending)
    {
        if (!$this->_checkLimits($pending)) {
            $pending->update([
                'status' => EnumTransactionsStatus::ABOVELIMIT
            ]);

            return false;
        }

        $this->sendBTC($pending);
    }

    /**
     *
     * @param type $pending
     * @throws \Exception
     */
    public function sendBTC($pending)
    {
        $wallet = UserWallet::findOrFail($pending->wallet_id);
        $user = User::where(['id' => $wallet->user_id])->first();

        try {

            if ($user->is_under_analysis) {
                throw new \Exception('Usuario em analise atualmente, transacao nao sera enviada ate o fim da analise.');
            }

            $coin_abbr = Coin::find($pending->coin_id)->abbr;
            $data = [
                'fromAddress' => $pending->address,
                'toAddress' => $pending->toAddress,
                'fee' => $pending->fee,
                'amount' => $pending->amount,
                'balance' => sprintf("%.8f", $wallet->balance)
            ];

            $tx = OffScreenController::post(EnumOperationType::FIRST_SIGN_TRANSACTION, $data, $coin_abbr);

            if ($tx == 155) {
                throw new \Exception(155);
            }

            $pending->update([
                'tx' => $tx,
                'error' => '',
                'status' => EnumTransactionsStatus::SUCCESS
            ]);
        } catch (\Exception $ex) {

            if (env('CHECK_WALLETS_BALANCES')) {

                if ($ex->getMessage() == 155) {
                    $user = User::where(['id' => $wallet->user_id, 'is_under_analysis' => false])->first();

                    if ($user) {
                        $user->is_under_analysis = true;
                        $user->save();

                        $user->tokens()->each(function ($token) {
                            $token->delete();
                        });

                        Mail::to($user->email)->send(new UnderAnalysisMail($user));
                    }
                }
            }

            $pending->update([
                'status' => EnumTransactionsStatus::ERROR,
                'error' => $ex->getMessage(),
            ]);
        }
    }

    public function _checkLimits($pending)
    {
        $wallet = UserWallet::findOrFail($pending->wallet_id);

        if ($wallet->user_id === env("NAVI_USER")) {
            return true;
        }

        $user = User::find($wallet->user_id);
        $limits = UserLevelLimit::where([
            'user_level_id' => $user->user_level_id,
            'coin_id' => $wallet->coin_id,
            'type' => EnumUserLevelLimitType::EXTERNAL,
        ])->first();
        $auto = floatval($limits->limit_auto);
        $amount = floatval($pending->amount);
        return ($auto >= $amount) ? true : false;
    }

}
