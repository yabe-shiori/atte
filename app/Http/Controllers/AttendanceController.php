<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceService;
use Illuminate\Pagination\Paginator;
use App\Models\User;

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

    // 勤務開始
    public function startWork()
    {
        // ログインしているユーザーの情報を取得
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

        return redirect()->route('dashboard')->with('message', '出勤しました！');
    }

    // 勤務終了
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

        // 勤怠レコードに終了時刻を追加
        $todayAttendance->end_time = now();
        $todayAttendance->save();
        $message = $user->name . 'さん、お疲れさまでした！';

        return redirect()->route('dashboard')->with('message', $message);
    }
    // 日付別勤怠一覧
    public function attendanceList(Request $request)
    {
        // 選択された日付を取得
        $selectedDate = $request->input('date', now()->toDateString());

        // AttendanceService 経由でデータを取得
        $attendancesData = $this->attendanceService->getAttendancesByDate($selectedDate);

        // AttendanceService 経由で時間を計算
        $attendancesData = $this->attendanceService->calculateTimes($attendancesData);

        // ページネーションを使ってデータを取得
        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('work_date', $selectedDate)
            ->paginate(5);

        return view('attendance_list', compact('attendancesData', 'attendances', 'selectedDate'));
    }
    // public function userAttendance(User $user)
    // {
    //     $attendances = $user->attendance;

    //     return view('profile/user_attendance', compact('user', 'attendances'));
    // }

}
