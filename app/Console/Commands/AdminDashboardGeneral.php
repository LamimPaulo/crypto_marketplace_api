<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Console\Command;

class AdminDashboardGeneral extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboardup:general';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'General Data Update';

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
        $dashboard = new DashboardController();
        $dashboard->general();
    }
}
