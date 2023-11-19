<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;

class AttendanceService
{
    public function calculateTimes($attendances)
    {
        $calculatedAttendances = [];

        foreach ($attendances as $attendance) {
            $calculatedAttendance = $attendance;
            $calculatedAttendance['breakDuration'] = $this->calculateBreakDuration($attendance);
            $calculatedAttendance['workDuration'] = $this->calculateWorkDuration($attendance);
            $calculatedAttendances[] = $calculatedAttendance;
        }

        return $calculatedAttendances;
    }

    public function calculateWorkDuration($attendance)
    {
        $start = Carbon::parse($attendance->start_time);
        $end = Carbon::parse($attendance->end_time);

        return $end->diff($start)->format('%H:%I:%S');
    }

    public function calculateBreakDuration($attendance)
    {
        $breakTimes = $attendance->breakTimes;

        // 休憩が存在する場合
        if ($breakTimes->isNotEmpty()) {
            // 各休憩時間を合計
            $breakDurationInSeconds = $breakTimes->sum(function ($breakTime) {
                $breakStart = Carbon::parse($breakTime->break_start_time);
                $breakEnd = $breakTime->break_end_time ? Carbon::parse($breakTime->break_end_time) : now();
                return $breakStart->diffInSeconds($breakEnd);
            });

            // 合計秒数を時分秒形式にフォーマットして返す
            return $this->formatDuration($breakDurationInSeconds);
        } else {
            return '00:00:00';
        }
    }

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

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
                unset($attendance->min_break_start_time, $attendance->max_break_end_time);

                return $attendance;
            });
    }
}
