<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        $todayAttendance = $user->attendance()->whereDate('work_date', $now->toDateString())->first();

        if (!$todayAttendance) {
            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->start_time = $now;

            $attendance->crossed_midnight = $this->hasCrossedMidnight($user, $now);

            // 出勤時刻が深夜をまたいでいる場合、分割して保存
            if ($attendance->crossed_midnight) {
                $attendance->work_date = $now->toDateString();
                // 変更: 深夜0時になるように設定
                $attendance->end_time = $now->copy()->startOfDay()->addDay();
                $attendance->save();

                // 新しいレコードを作成
                $nextDayAttendance = new Attendance();
                $nextDayAttendance->user_id = $user->id;
                // 変更: 次の日の出勤開始時間を設定
                $nextDayAttendance->start_time = $attendance->end_time;
                $nextDayAttendance->work_date = $attendance->end_time->toDateString();
                $nextDayAttendance->save();

                return redirect()->route('dashboard')->with('message', '出勤しました！');
            } else {
                $attendance->work_date = $now->toDateString();
                $attendance->save();
                return redirect()->route('dashboard')->with('message', '出勤しました！');
            }
        }
        return redirect()->route('dashboard')->with('error', '本日の勤務は既に開始しています。');
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

            $todayAttendance->end_time = $now;
            $todayAttendance->save();

            return redirect()->route('dashboard')->with('message', $user->name . 'さん、お疲れさまでした！');
        }

        return redirect()->route('dashboard')->with('error', '勤務が開始されていません。');
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


    private function hasCrossedMidnight($user, Carbon $now)
    {
        $today = $now->toDateString();
        $startOfToday = $now->copy()->startOfDay();
        $endOfToday = $now->copy()->endOfDay();
        $startOfTomorrow = $now->copy()->addDay()->startOfDay();

        return $user->attendance()
            ->whereDate('work_date', $today)
            ->where(function ($query) use ($startOfToday, $endOfToday, $startOfTomorrow) {
                $query->where('start_time', '<', $startOfToday)
                    ->orWhere(function ($query) use ($endOfToday, $startOfTomorrow) {
                        $query->where('end_time', '>', $endOfToday)
                            ->orWhereNull('end_time');
                    });
            })
            ->exists();
    }
}
