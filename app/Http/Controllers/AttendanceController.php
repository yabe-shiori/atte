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

        return view('dashboard', [
            'user' => $user,
        ]);
    }

    public function startWork()
    {
        $user = Auth::user();

        $todayAttendance = $user->attendance()->whereDate('start_time', now()->toDateString())->first();

        if ($todayAttendance) {
            return redirect()->route('dashboard')->with('error', '本日の勤務は既に開始しています。');
        }

        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->start_time = now();

        // 初回勤務開始時に日付をまたいでいるか確認し、crossed_midnightカラムを設定
        $attendance->crossed_midnight = $this->hasCrossedMidnight($user);

        // 10時間経過後に勤務終了時刻を設定
        $tenHoursLater = now()->addHours(10);
        if ($attendance->crossed_midnight) {
            // 日付をまたいでいる場合、勤務終了ボタンが押されていないか確認
            if ($attendance->end_time === null) {
                // 勤務終了ボタンが押されていない場合、自動でその時間を勤務終了時間に設定
                $attendance->end_time = now();

                return redirect()->route('dashboard')->with('message', '勤務終了ボタンが押されていません。前日の勤務終了時刻を管理者に伝えてください。');
            }
        }

        $attendance->work_date = now()->toDateString();
        $attendance->save();

        return redirect()->route('dashboard')->with('message', '出勤しました！');
    }

    public function endWork()
    {
        $user = Auth::user();

        $todayAttendance = $user->attendance()
            ->whereDate('work_date', now()->toDateString())
            ->whereNull('end_time')
            ->first();

        if ($todayAttendance) {
            $breaks = $todayAttendance->breakTimes;

            foreach ($breaks as $break) {
                if (is_null($break->break_end_time)) {
                    return redirect()->route('dashboard')->with('error', '休憩が終了していません。');
                }
            }

            $todayAttendance->end_time = now();
            $todayAttendance->save();

            return redirect()->route('dashboard')->with('message', $user->name . 'さん、お疲れさまでした！');
        }

        return redirect()->route('dashboard')->with('error', '勤務が開始されていません。');
    }

    public function attendanceList(Request $request)
    {
        $selectedDate = $request->input('date', now()->toDateString());

        $totalAttendances = Attendance::whereDate('work_date', $selectedDate)->count();

        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('work_date', $selectedDate)
            ->paginate(5);

        return view('attendance_list', compact('attendances', 'selectedDate', 'totalAttendances'));
    }

    // 新しく追加
    private function hasCrossedMidnight($user)
    {
        return $user->attendance()
            ->where('start_time', '<', '22:00:00')
            ->where('end_time', '>=', '06:00:00')
            ->exists();
    }
}
