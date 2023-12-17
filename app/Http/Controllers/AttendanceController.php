<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $now = now();
        $user = Auth::user();

        if ($this->hasUnfinishedWork($user)) {
            return redirect()->route('dashboard')->with('error', '終了していない勤務があります。');
        }

        $todayAttendance = $user->attendance()->whereDate('work_date', $now->toDateString())->first();

        if (!$todayAttendance) {
            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->start_time = $now;
            $attendance->work_date = $now->toDateString();
            $attendance->save();

            return redirect()->route('dashboard')->with('message', '出勤しました！');
        }

        return redirect()->route('dashboard')->with('error', '本日の勤務は既に開始しています。');
    }

    private function hasUnfinishedWork($user)
    {
        return $user->attendance()
            ->whereNull('end_time')
            ->exists();
    }

    public function endWork()
    {
        $now = now();
        $user = Auth::user();

        $todayAttendance = $user->attendance()
            ->whereDate('work_date', $now->toDateString())
            ->whereNull('end_time')
            ->first();

        if ($todayAttendance) {
            $breaks = $todayAttendance->breakTimes;

            foreach ($breaks as $break) {
                if (is_null($break->break_end_time)) {
                    return redirect()->route('dashboard')->with('error', '休憩が終了していません。');
                }
            }

            if ($this->hasCrossedMidnight($user, $now)) {
                $this->splitMidnight($user, $todayAttendance, $now);
            } else {
                // 勤務が日をまたいでいない場合、現在の時刻を終了時刻として記録
                $todayAttendance->end_time = $now;
                $todayAttendance->save();
            }

            return redirect()->route('dashboard')->with('message', $user->name . 'さん、お疲れさまでした！');
        } else {
            return redirect()->route('dashboard')->with('error', '勤務が開始されていません。');
        }
    }


    private function splitMidnight($user, $attendance, Carbon $now)
    {
        // 前日の勤務終了時刻を23:59:59に設定
        $attendance->end_time = $now->copy()->subDay()->endOfDay();
        $attendance->save();

        // 翌日のレコード作成
        $nextDayAttendance = new Attendance();
        $nextDayAttendance->user_id = $user->id;
        $nextDayAttendance->start_time = $now->copy()->startOfDay(); // 翌日の勤務開始時刻を00:00:00に設定
        $nextDayAttendance->end_time = $now;
        $nextDayAttendance->work_date = $now->toDateString();
        $nextDayAttendance->save();
    }


    private function hasCrossedMidnight($user, Carbon $now)
    {
        // 終了時間が未記録の最後の出勤記録を取得
        $lastAttendance = $user->attendance()
            ->whereNull('end_time')
            ->orderBy('work_date', 'desc')
            ->first();

        return $lastAttendance && $lastAttendance->work_date < $now->toDateString();
    }

    public function attendanceList(Request $request)
    {
        $now = now();
        $selectedDate = $request->input('date', $now->toDateString());

        $totalAttendances = Attendance::whereDate('work_date', $selectedDate)->count();

        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('work_date', $selectedDate)
            ->paginate(5);

        return view('attendance_list', compact('attendances', 'selectedDate', 'totalAttendances'));
    }
}
