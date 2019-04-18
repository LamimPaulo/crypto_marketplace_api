<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\Funds\FundTransactionsController;
use Illuminate\Console\Command;

class FundProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'funds:profits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera os Lucros de investimentos';

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
     */
    public function handle()
    {
        $profit = new FundTransactionsController();
        $profit->profitsCommand();
    }
}
