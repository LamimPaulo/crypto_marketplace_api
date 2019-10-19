<?php

namespace App\Console\Commands;

use App\Http\Controllers\MasternodeController;
use Illuminate\Console\Command;

class MasternodeCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masternode:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Masternodes';

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
        MasternodeController::proccessPending();
    }
}
