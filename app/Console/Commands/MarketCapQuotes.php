<?php

namespace App\Console\Commands;

use App\Http\Controllers\CoinQuoteController;
use Illuminate\Console\Command;

class MarketCapQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:marketcapquotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        if(env("APP_ENV")!=='local') {
            $coinQuotesController = new CoinQuoteController();
            $coinQuotesController->MARKETCAP_CRYPTO_QUOTES();
        }
    }
}
