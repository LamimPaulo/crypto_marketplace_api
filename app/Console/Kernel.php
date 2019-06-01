<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('nanotech:percentages')->monthlyOn(1, '00:30');
        $schedule->command('nanotech:profits')->dailyAt('01:00');
        $schedule->command('nanotech:profits')->dailyAt('02:00');

        $schedule->command('funds:profits')->monthlyOn(10, '00:30');
        $schedule->command('funds:expiration')->dailyAt('01:00');

        $schedule->command('gateway:expirepayments')->everyMinute();
        $schedule->command('estimate:fee')->everyMinute();
        $schedule->command('get:btcquote')->everyFiveMinutes();
        $schedule->command('trade:execute')->everyMinute();
        $schedule->command('get:marketcapquotes')->everyTenMinutes();
        $schedule->command('transactions:send')->everyMinute();
        $schedule->command('transactions:confirmation')->everyMinute();

        $schedule->command('masternode:update')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('update:corebalances')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('dashboardup:general')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('dashboardup:withdrawals')->everyMinute()->withoutOverlapping();
        $schedule->command('dashboardup:deposits')->everyMinute()->withoutOverlapping();
        $schedule->command('dashboardup:nanotech')->everyMinute()->withoutOverlapping();
        $schedule->command('dashboardup:cryptooperations')->everyFiveMinutes()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
