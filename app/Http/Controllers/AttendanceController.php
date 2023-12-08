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
                $attendance->end_time = $now->copy()->endOfDay();
                $attendance->save();

                $startOfNextDay = $now->copy()->addDay()->startOfDay();

                $nextDayAttendance = new Attendance();
                $nextDayAttendance->user_id = $user->id;
                $nextDayAttendance->start_time = $startOfNextDay;
                $nextDayAttendance->work_date = $startOfNextDay->toDateString();
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
        $startOfTomorrow = $now->copy()->addDay()->startOfDay();

        return $user->attendance()
            ->whereDate('work_date', $today)
            ->where(function ($query) use ($startOfToday, $startOfTomorrow) {
                $query->where('start_time', '<', $startOfToday)
                    ->orWhere(function ($query) use ($startOfTomorrow) {
                        $query->where('end_time', '>=', $startOfTomorrow)
                            ->orWhereNull('end_time');
                    });
            })
            ->exists();
    }
}
