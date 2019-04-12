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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('paypal:confirmations')->everyMinute();
        $schedule->command('estimate:fee')->everyMinute();
        $schedule->command('get:btcquote')->everyFiveMinutes();
        $schedule->command('get:binancequote')->everyMinute();
        $schedule->command('update:fundquotes')->everyMinute();
        $schedule->command('get:binanceconfirmations')->everyMinute();
        $schedule->command('pool:refresh')->everyMinute();
        $schedule->command('mining:profits')->everyMinute();
        $schedule->command('trade:execute')->everyMinute()->withoutOverlapping();
        $schedule->command('get:marketcapquotes')->everyTenMinutes();
        $schedule->command('gateway:expirepayments')->everyTenMinutes();
        $schedule->command('transactions:send')->everyMinute();
        $schedule->command('transactions:confirmation')->everyMinute();
        $schedule->command('investment:percentages')->monthlyOn(1, '00:30');
        $schedule->command('investment:profits')->dailyAt('01:00');
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
