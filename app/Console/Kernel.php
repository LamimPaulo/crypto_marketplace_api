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
        //$schedule->command('nanotech:percentages')->monthlyOn(1, '00:30');
        //$schedule->command('nanotech:profits')->dailyAt('01:00');
        //$schedule->command('nanotech:profits')->dailyAt('02:00');
        $schedule->command('update:lqxbalance')->dailyAt('05:00');

        $schedule->command('lqxd:generate')->dailyAt('03:00');
        $schedule->command('lqx:withdrawals')->dailyAt('03:00');

        $schedule->command('funds:profits')->monthlyOn(10, '00:30');
        $schedule->command('funds:expiration')->dailyAt('01:00');

        $schedule->command('gateway:expirepayments')->everyTenMinutes();
        $schedule->command('pool:refresh')->everyMinute();
        $schedule->command('estimate:fee')->everyTenMinutes();
        $schedule->command('get:btcquote')->everyFiveMinutes();
        $schedule->command('transactions:authorize')->everyMinute()->withoutOverlapping();
        $schedule->command('transactions:send')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('transactions:confirmation')->everyMinute()->withoutOverlapping();

        $schedule->command('update:corebalances')->everyTenMinutes()->withoutOverlapping();
        $schedule->command('sync:lqxwallets')->everyTenMinutes()->withoutOverlapping();
        $schedule->command('trade:execute')->everyFiveMinutes();

        $schedule->command('masternode:create')->everyMinute();
        $schedule->command('masternode:update')->everyFiveMinutes();
        $schedule->command('masternode:info')->everyFiveMinutes();

        $schedule->command('masternode:generateplans')->everyTenMinutes();
        $schedule->command('masternode:verifypayments')->everyTenMinutes();

        $schedule->command('gateway:reverseunderpaid')->everyFiveMinutes();
        $schedule->command('gateway:reverseoverpaid')->everyFiveMinutes();

        $schedule->command('messages:create')->everyMinute();


    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
