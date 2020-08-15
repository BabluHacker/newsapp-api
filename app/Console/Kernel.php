<?php

namespace App\Console;

use Aws\Command;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ImageUploads::class,
        Commands\Notification::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('image:uploads')
//            ->timezone('Asia/Dhaka')
//            ->everyTenMinutes(); // every minutes
        $schedule->command('notification:create')
            ->timezone('Asia/Dhaka')
            ->cron('5 9,14,19,21 * * *');
    }
}
