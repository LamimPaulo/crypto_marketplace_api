<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PaypalConfirmations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paypal:confirmations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Confirmar Pagamentos vindos do Paypal';

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
        //
    }
}
