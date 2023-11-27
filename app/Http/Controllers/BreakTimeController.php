<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class BreakTimeController extends Controller
{
    public function store(Request $request)
    {
        if ($this->isWorkEnded()) {
            return redirect()->route('dashboard')->with('error', '勤務が終了しています。');
        }

        $breakTime = new BreakTime([
            'attendance_id' => $request->input('attendance_id'),
            'break_start_time' => $request->input('break_start_time'),
            'break_end_time' => $request->input('break_end_time'),
        ]);

        $breakTime->save();

        return redirect()->route('dashboard');
    }

    public function startBreak()
    {
        if ($this->isWorkEnded()) {
            return redirect()->route('dashboard')->with('error', '勤務が終了しています。');
        }

        $todayAttendance = $this->getTodayAttendance();

        if (!$todayAttendance) {
            return redirect()->route('dashboard')->with('error', '勤務が開始されていません。');
        }

        if ($this->isBreakStarted()) {
            return redirect()->route('dashboard')->with('error', '既に休憩が開始されています。');
        }

        $user = Auth::user();
        $user->break_started = true;
        $user->save();

        $breakTime = new BreakTime();
        $breakTime->attendance_id = $todayAttendance->id;
        $breakTime->break_start_time = now();
        $breakTime->save();

        return redirect()->route('dashboard')->with('message', '休憩を開始しました。');
    }

    public function endBreak()
    {
        if ($this->isWorkEnded()) {
            return redirect()->route('dashboard')->with('error', '勤務が終了しています。');
        }

        $todayBreakTime = $this->getLatestBreakTime();

        if (!$todayBreakTime || $todayBreakTime->break_end_time !== null) {
            return redirect()->route('dashboard')->with('error', '休憩が開始されていません。');
        }

        $user = Auth::user();
        $user->break_started = false;
        $user->save();

        $todayBreakTime->break_end_time = now();
        $todayBreakTime->save();

        return redirect()->route('dashboard')->with('message', '休憩を終了しました。');
    }

    private function isBreakStarted()
    {
        $user = Auth::user();
        return $user->break_started;
    }

    private function isWorkEnded()
    {
        $todayAttendance = $this->getTodayAttendance();

        return $todayAttendance && $todayAttendance->end_time !== null;
    }

    private function getTodayAttendance()
    {
        $user = Auth::user();

        return $user->attendance()
            ->whereDate('start_time', now()->toDateString())
            ->first();
    }

    private function getLatestBreakTime()
    {
        $todayAttendance = $this->getTodayAttendance();

        if ($todayAttendance) {
            $latestBreakTime = $todayAttendance->breakTimes()->latest()->first();
            return $latestBreakTime ?? null;
        }

        return null;
    }
}
