<?php

namespace App\Console\Commands;

use App\Http\Controllers\GatewayController;
use App\Http\Controllers\MasternodeController;
use App\Http\Controllers\Notify\BTCController;
use App\Models\Transaction;
use Illuminate\Console\Command;

class TransactionsConfirmation extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:confirmation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza o numero de confirmações referentes a crypto';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->gateway();

        $processing = Transaction::confirmation();
        foreach ($processing as $transaction) {
            $this->BTC($transaction);
            unset($connection);
        }

        $processing = Transaction::masternodes_confirmation();
        foreach ($processing as $transaction) {
            $this->masternodes($transaction);
            unset($connection);
        }
    }

    private function BTC($transaction) {
        BTCController::confirmation($transaction);
    }

    private function gateway() {
        GatewayController::confirmation();
    }

    private function masternodes($transaction) {
        MasternodeController::confirmation($transaction);
    }

}
