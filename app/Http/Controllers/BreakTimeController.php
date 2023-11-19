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
    // 休憩時間保存処理
    public function store(Request $request)
    {
        // データの検証（例：attendance_idが必須であると仮定）
        // $request->validate([
        //     'attendance_id' => 'required',
        //     // 他にも必要なバリデーションルールを追加
        // ]);

        // 勤怠レコードが終了している場合は休憩開始を許可しない
        if ($this->isWorkEnded()) {
            return redirect()->route('dashboard')->with('error', '勤務が終了しています。');
        }

        // 休憩レコードを作成
        $breakTime = new BreakTime([
            'attendance_id' => $request->input('attendance_id'),
            'break_start_time' => $request->input('break_start_time'),
            'break_end_time' => $request->input('break_end_time'),
        ]);

        // 休憩時間を保存
        $breakTime->save();

        // 適切なリダイレクトなどを行う
        return redirect()->route('dashboard');
    }

    public function startBreak()
    {
        // 勤怠レコードが終了している場合は休憩開始を許可しない
        if ($this->isWorkEnded()) {
            return redirect()->route('dashboard')->with('error', '勤務が終了しています。');
        }

        // 既に開始されている休憩があるか確認
        if ($this->isBreakStarted()) {
            return redirect()->route('dashboard')->with('error', '既に休憩が開始されています。');
        }

        // 休憩レコードを作成
        $breakTime = new BreakTime();
        $breakTime->attendance_id = $this->getTodayAttendance()->id;
        $breakTime->break_start_time = now();
        $breakTime->save();

        // 休憩開始の状態をセット
        session(['break_started' => true]);

        return redirect()->route('dashboard')->with('message', '休憩を開始しました。');
    }

    // 休憩終了ボタンがクリックされたときの処理
    public function endBreak()
    {
        // 勤怠レコードが終了している場合は休憩終了を許可しない
        if ($this->isWorkEnded()) {
            return redirect()->route('dashboard')->with('error', '勤務が終了しています。');
        }

        // 休憩が開始されていない場合
        $todayBreakTime = $this->getLatestBreakTime();

        if (!$todayBreakTime || $todayBreakTime->break_end_time !== null) {
            return redirect()->route('dashboard')->with('error', '休憩が開始されていません。');
        }

        // 休憩終了時刻を更新
        $todayBreakTime->break_end_time = now();
        $todayBreakTime->save();

        // 休憩終了の状態をリセット
        session(['break_started' => false]);

        return redirect()->route('dashboard')->with('message', '休憩を終了しました。');
    }

    // 休憩が開始されているかどうかを判定
    private function isBreakStarted()
    {
        return session('break_started', false);
    }


    // 勤務が終了しているかどうかを判定
    private function isWorkEnded()
    {
        $todayAttendance = $this->getTodayAttendance();

        return $todayAttendance && $todayAttendance->end_time !== null;
    }

    // 当日の勤怠レコードを取得
    private function getTodayAttendance()
    {
        $user = Auth::user();

        return $user->attendance()
            ->whereDate('start_time', now()->toDateString())
            ->first();
    }

    // 当日の最後の休憩レコードを取得
    private function getLatestBreakTime()
    {
        $todayAttendance = $this->getTodayAttendance();

        if ($todayAttendance) {
            // 最後の休憩レコードを取得
            $latestBreakTime = $todayAttendance->breakTimes()->latest()->first();

            return $latestBreakTime ?? null;
        }

        return null;
    }
}
