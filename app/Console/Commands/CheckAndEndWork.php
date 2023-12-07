<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Notifications\EndWorkTimeSetNotification;

class CheckAndEndWork extends Command
{
    protected $signature = 'check:endwork';
    protected $description = 'Check and end work for users who have been working for more than 10 hours';

    public function handle()
    {
        $this->info('Checking and ending work...');

        $usersToCheck = Attendance::whereNull('end_time')
            ->where('start_time', '<', now()->subHours(10)->toDateTimeString())
            ->get();

        foreach ($usersToCheck as $attendance) {
            $attendance->end_time = $attendance->start_time->addHours(10);
            $attendance->save();

            // ユーザーに通知
            // $attendance->user->notify(new EndWorkTimeSetNotification($attendance));

            $this->info('Work ended for user ' . $attendance->user->name);
        }

        $this->info('Check and end work completed.');
    }
}
