<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Attendance;
use App\Models\User;


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

        if ($this->isBreakStarted()) {
            return redirect()->route('dashboard')->with('error', '既に休憩が開始されています。');
        }

        $user = Auth::user();

        $todayAttendance = $this->getTodayAttendance();

        if ($todayAttendance === null) {

            return redirect()->route('dashboard')->with('error', '勤務が開始されていません。');
        }

        $user->break_started = true;
        $user->save();

        // 休憩レコードを作成
        $breakTime = new BreakTime();
        $breakTime->attendance_id = $todayAttendance->id;
        $breakTime->break_start_time = now();
        $breakTime->save();

        return redirect()->route('dashboard')->with('message', '休憩を開始しました。');
    }

    // 休憩終了ボタンがクリックされたときの処理
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

        // 休憩終了時刻を更新
        $todayBreakTime->break_end_time = now();
        $todayBreakTime->save();

        return redirect()->route('dashboard')->with('message', '休憩を終了しました。');
    }

    // 休憩が開始されているかどうかを判定
    private function isBreakStarted()
    {
        $user = Auth::user();
        return $user->break_started;
    }


    // 勤務が終了しているかどうかを判定
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
