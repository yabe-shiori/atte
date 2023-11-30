<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;

class BreakTimeController extends Controller
{
    public function store(Request $request)
    {
        if ($this->isWorkEnded()) {
            return redirect()->route('dashboard')->with('error', '勤務が終了しています。');
        }

        $breakTime = new BreakTime([
            'user_id' => Auth::user()->id,
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

        if ($this->isBreakStarted()) {
            return redirect()->route('dashboard')->with('error', '既に休憩が開始されています。');
        }

        $todayAttendance = $this->getTodayAttendance();

        if ($todayAttendance === null) {
            return redirect()->route('dashboard')->with('error', '勤務が開始されていません。');
        }

        $breakTime = new BreakTime([
            'user_id' => Auth::user()->id,
            'attendance_id' => $todayAttendance->id,
            'break_start_time' => now(),
        ]);

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

        $todayBreakTime->break_end_time = now();
        $todayBreakTime->save();

        return redirect()->route('dashboard')->with('message', '休憩を終了しました。');
    }

    private function isBreakStarted()
    {
        $user = Auth::user();
        $todayAttendance = $this->getTodayAttendance();

        return $todayAttendance && $todayAttendance->breakTimes()->whereNull('break_end_time')->exists();
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
            return $todayAttendance->breakTimes()->latest()->first();
        }

        return null;
    }
}
