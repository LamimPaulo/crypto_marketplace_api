<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\Messages\MessageController;
use Illuminate\Console\Command;

class CreateMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create General Messages';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            MessageController::createMessageStatus();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
