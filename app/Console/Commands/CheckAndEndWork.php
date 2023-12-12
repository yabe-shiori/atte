<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Notifications\EndWorkTimeSetNotification;

class CheckAndEndWork extends Command
{
    protected $signature = 'check:endwork';
    protected $description = '10時間以上働いているユーザーをチェックして勤務終了時刻を自動で設定します。';

    public function handle()
    {
        $this->info('Checking and ending work...');

        $attendances = Attendance::with('user')
            ->whereNull('end_time')
            ->where('start_time', '<', now()->subHours(10))
            ->get();

        foreach ($attendances as $attendance) {
            $calculatedEndTime = $attendance->start_time->copy()->addHours(10);

            if ($attendance->crossed_midnight) {
                $nextDayAttendance = Attendance::where('user_id', $attendance->user_id)
                    ->whereDate('work_date', '=', $attendance->start_time->addDay()->toDateString())
                    ->first();

                if (!$nextDayAttendance) {
                    $nextDayAttendance = new Attendance();
                    $nextDayAttendance->user_id = $attendance->user_id;
                    $nextDayAttendance->work_date = $attendance->start_time->addDay()->toDateString();
                    $nextDayAttendance->start_time = $attendance->start_time->copy()->addDay()->startOfDay();
                    $nextDayAttendance->end_time = $calculatedEndTime;
                    $nextDayAttendance->save();
                }
            } else {
                $attendance->end_time = $calculatedEndTime;
                $attendance->save();
            }

            $attendance->user->notify(new EndWorkTimeSetNotification($attendance));

            $this->info('Work ended automatically for user: ' . $attendance->user->name);
        }

        $this->info('Check and end work process completed.');
    }
}
