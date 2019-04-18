<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\Funds\FundsController;
use Illuminate\Console\Command;

class FundExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'funds:expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expira os contratos de investimentos';

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
        $funds = new FundsController();
        $funds->expirationCommand();
    }
}
