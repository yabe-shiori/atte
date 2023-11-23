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
            return view('dashboard', ['user' => $user,]);
        } else {
            return view('auth.login');
        }
    }

    //出勤処理
    public function startWork()
    {
        $user = Auth::user();

        // 当日の勤怠レコードが存在するか確認
        $todayAttendance = $user->attendance()->whereDate('start_time', now()->toDateString())->first();

        if ($todayAttendance) {
            return redirect()->route('dashboard')->with('error', '本日の勤怠は既に開始しています。');
        }

        // crossed_midnight カラムが true なら日をまたいでいるとみなす
        $crossedMidnight = $user->attendance()
            ->where('crossed_midnight', true)
            ->exists();

        // 勤怠レコードを作成
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->start_time = now();

        // 日をまたいでいる場合は crossed_midnight カラムを true に設定
        if ($crossedMidnight) {
            $attendance->crossed_midnight = true;
        }

        $attendance->work_date = now()->toDateString();
        $attendance->save();

        $userModel = \App\Models\User::find($user->id);
        $userModel->update(['work_started' => true]);
        // $user->work_started = true;
        // $user->save();

        return redirect()->route('dashboard')->with(['user' => $user, 'message' => '出勤しました！']);
    }
    //退勤処理
    public function endWork()
    {
        $user = Auth::user();

        $todayAttendance = $user->attendance()
            ->whereDate('work_date', now()->toDateString())
            ->whereNull('end_time')
            ->first();

        if (!$todayAttendance) {
            return redirect()->route('dashboard')->with('error', '本日の勤怠を開始していません。');
        }

        $todayAttendance->end_time = now();
        $todayAttendance->save();
        $message = $user->name . 'さん、お疲れさまでした！';

        $userModel = \App\Models\User::find($user->id);
        $userModel->update(['work_started' => false]);
        // $user->work_started = false;
        // $user->save();

        return redirect()->route('dashboard')->with('message', $message);
    }

    // 日付別勤怠一覧
    public function attendanceList(Request $request)
    {
        $selectedDate = $request->input('date', now()->toDateString());

        // AttendanceService 経由でデータを取得
        $attendancesData = $this->attendanceService->getAttendancesByDate($selectedDate);

        // AttendanceService 経由で時間を計算
        $attendancesData = $this->attendanceService->calculateTimes($attendancesData);

        // ページネーションを使ってデータを取得
        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('work_date', $selectedDate)
            ->paginate(5);

        // 時間の計算を行ったデータをビューに渡す
        return view('attendance_list', compact('attendancesData', 'attendances', 'selectedDate'));
    }
}
