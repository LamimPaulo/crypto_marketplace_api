<?php

namespace App\Console\Commands;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Exchange\ExchangesController;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TradesExecution extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trade:execute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute Trades';

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
        $this->updateTrades();
        sleep(2);
        $this->updateTrades();
        sleep(2);
        $this->updateTrades();
        sleep(2);
        $this->updateTrades();
        sleep(2);
        $this->updateTrades();
    }

    public function updateTrades()
    {
        $exchanges = new ExchangesController();
        $exchanges->execute();
    }

}
