<?php

namespace App\Console\Commands;

use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumUserLevelLimitType;
use App\Models\Transaction;
use App\Models\User\UserLevelLimit;
use App\Models\User\UserWallet;
use App\User;
use Illuminate\Console\Command;

class TransactionsAuthorize extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:authorize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Autoriza transações pendentes para o core correspondente';

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
    }

    /**
     * Envia transações baseadas em BitCoin
     * @param type $pending
     * @return bool
     * @throws \Exception
     */
    private function BTC($pending)
    {
        if ($pending->user_id == env("NAVI_USER")) {
            $pending->update([
                'status' => EnumTransactionsStatus::AUTHORIZED
            ]);
            return true;
        }

        if (!$this->_checkLimits($pending)) {
            $pending->update([
                'status' => EnumTransactionsStatus::ABOVELIMIT
            ]);

            return false;
        }

        if (!$this->_checkLimits($pending)) {
            $pending->update([
                'status' => EnumTransactionsStatus::ABOVELIMIT
            ]);

            return false;
        }

        $pending->update([
            'status' => EnumTransactionsStatus::AUTHORIZED
        ]);
    }

    public function _checkLimits($pending)
    {
        $wallet = UserWallet::findOrFail($pending->wallet_id);

        if ($wallet->user_id == env("NAVI_USER")) {
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
