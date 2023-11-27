<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceService;


class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        $user = Auth::user();
        if ($user) {
            $workStatus = $user->work_started;
            return view('dashboard', ['user' => $user, 'workStatus' => $workStatus]);
        } else {
            return view('auth.login');
        }
    }


    public function startWork()
    {
        $user = Auth::user();

    //   $yesterdayAttendance = $user->attendance()
    //         ->whereDate('work_date', now()->subDay()->toDateString())
    //         ->first();

    //     if ($yesterdayAttendance && !$yesterdayAttendance->end_time) {
    //         $attendance = new Attendance();
    //         $attendance->user_id = $user->id;
    //         $attendance->start_time = now();
    //         $attendance->work_date = now()->toDateString();
    //         $attendance->save();

    //         $user = \App\Models\User::find($user->id);
    //         $user->update(['work_started' => true]);

    //         return redirect()->route('dashboard')->with('message', '前日の勤務終了が押されていませんが、新しい勤務を開始しました。');
    //     }
        $todayAttendance = $user->attendance()->whereDate('start_time', now()->toDateString())->first();

        if ($todayAttendance) {
            return redirect()->route('dashboard')->with('error', '本日の勤務は既に開始しています。');
        }

        $crossedMidnight = $user->attendance()
            ->where('crossed_midnight', true)
            ->exists();

        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->start_time = now();

        if ($crossedMidnight) {
            $attendance->crossed_midnight = true;
        }

        $attendance->work_date = now()->toDateString();
        $attendance->save();

        $userModel = \App\Models\User::find($user->id);
        $userModel->update(['work_started' => true]);

        return redirect()->route('dashboard')->with('message', '出勤しました！');
    }

    public function endWork()
    {
        $user = Auth::user();

        // 本日の未完了の勤務がある場合、それを終了させる
        $todayAttendance = $user->attendance()
            ->whereDate('work_date', now()->toDateString())
            ->whereNull('end_time')
            ->first();

        if ($todayAttendance) {
            $todayAttendance->end_time = now();
            $todayAttendance->save();
            $message = $user->name . 'さん、お疲れさまでした！';

            $userModel = \App\Models\User::find($user->id);
            $userModel->update(['work_started' => false]);

            return redirect()->route('dashboard')->with('message', $message);
        }

        // 本日の未完了の勤務がない場合、エラーメッセージを表示する
        return redirect()->route('dashboard')->with('error', '本日の勤務を終了していません。');
    }
    // 日付別勤怠一覧
    public function attendanceList(Request $request)
    {
        $selectedDate = $request->input('date', now()->toDateString());

        // ページネーションの設定前に全体のデータ数を取得
        $totalAttendances = Attendance::whereDate('work_date', $selectedDate)->count();

        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('work_date', $selectedDate)
            ->paginate(5);

        return view('attendance_list', compact('attendances', 'selectedDate', 'totalAttendances'));
    }
}

