<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use Illuminate\Support\Collection;

class AttendanceService
{
    public function calculateTimes(Collection $attendances)
    {
        return $attendances->map(function ($attendance) {
            return array_merge($attendance->toArray(), [
                'breakDuration' => $this->calculateBreakDuration($attendance),
                'workDuration' => $this->calculateWorkDuration($attendance),
            ]);
        });
    }

    // 勤務開始から勤務終了までの時間を計算
    public function calculateWorkDuration($attendance)
    {
        $start = Carbon::parse($attendance->start_time);
        $end = Carbon::parse($attendance->end_time);

        return $end->diff($start)->format('%H:%I:%S');
    }

    // 休憩時間を計算
    public function calculateBreakDuration($attendance)
    {
        $breakTimes = $attendance->breakTimes;

        if ($breakTimes->isNotEmpty()) {
            $breakDurationInSeconds = $breakTimes->sum(function ($breakTime) {
                $breakStart = Carbon::parse($breakTime->break_start_time);
                $breakEnd = $breakTime->break_end_time ? Carbon::parse($breakTime->break_end_time) : now();
                return $breakStart->diffInSeconds($breakEnd);
            });

            return $this->formatDuration($breakDurationInSeconds);
        }

        return '00:00:00';
    }

    // 勤務時間を計算
    public function calculateWorkTime($attendance)
    {
        $start = Carbon::parse($attendance->start_time);
        $end = Carbon::parse($attendance->end_time);
        $breakTimes = $attendance->breakTimes;

        // 日付が異なる場合、日付ごとに勤務時間と休憩時間を計算
        if (!$start->isSameDay($end)) {
            $workDurationFirstDay = $start->copy()->endOfDay()->diffInSeconds($start) - $this->parseDuration($this->calculateBreakDuration($attendance));
            $workDurationSecondDay = $end->diffInSeconds($end->copy()->startOfDay()) - $this->parseDuration($this->calculateBreakDuration($attendance));

            // 合算
            $workDurationInSeconds = $workDurationFirstDay + $workDurationSecondDay;
        } else {
            // 同じ日に収まっている場合は通常通り休憩時間を計算
            $breakDuration = $breakTimes->isNotEmpty() ? $this->calculateBreakDuration($attendance) : '00:00:00';

            // 勤務時間を計算
            $workDurationInSeconds = max(0, $end->diffInSeconds($start) - $this->parseDuration($breakDuration));
        }

        return $this->formatDuration($workDurationInSeconds);
    }

    private function parseDuration($duration)
    {
        list($hours, $minutes, $seconds) = explode(':', $duration);
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    // 12/4 修正
    public function getAttendancesByDate($selectedDate)
    {
        return Attendance::with('user', 'breakTimes')
            ->select(
                'users.name',
                'attendances.work_date',
                'attendances.start_time',
                'attendances.end_time',
                DB::raw('MIN(break_times.break_start_time) as min_break_start_time'),
                DB::raw('MAX(break_times.break_end_time) as max_break_end_time')
            )
            ->leftJoin('users', 'attendances.user_id', '=', 'users.id')
            ->leftJoin('break_times', function ($join) use ($selectedDate) {
                $join->on('attendances.id', '=', 'break_times.attendance_id')
                    ->whereDate('break_times.break_start_time', '=', $selectedDate);
            })
            ->where('attendances.work_date', $selectedDate)
            ->groupBy('users.name', 'attendances.work_date', 'attendances.start_time', 'attendances.end_time', 'attendances.user_id')
            ->get()
            ->map(function ($attendance) {
                $attendance->break_start_time = $attendance->min_break_start_time;
                $attendance->break_end_time = $attendance->max_break_end_time;
                $attendance->breakDuration = $this->calculateBreakDuration($attendance);
                $attendance->workDuration = $this->calculateWorkTime($attendance);
                unset($attendance->min_break_start_time, $attendance->max_break_end_time);

                return $attendance;
            });
    }
}
