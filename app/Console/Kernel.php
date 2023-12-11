<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CheckAndEndWork;


class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        //開発環境でのみ使用
        if ($this->app->environment('local')) {
            $schedule->job(new CheckAndEndWork)->hourly();
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
