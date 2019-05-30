<?php

namespace App\Console\Commands;

use App\Http\Controllers\CoinQuoteController;
use Illuminate\Console\Command;

class getBtcQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:btcquote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Coin Quotes';

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

        $quoteCoinController = new CoinQuoteController();
        $quoteCoinController->USDTOBRL_QUOTE();
        $quoteCoinController->CRYPTO_QUOTES();
    }
}
