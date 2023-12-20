<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceService;
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

        $lastUnfinishedAttendance = $user->attendance()
            ->whereNull('end_time')
            ->orderBy('work_date', 'desc')
            ->first();

        if ($lastUnfinishedAttendance) {
            $breaks = $lastUnfinishedAttendance->breakTimes;

            foreach ($breaks as $break) {
                if (is_null($break->break_end_time)) {
                    return redirect()->route('dashboard')->with('error', '休憩が終了していません。');
                }
            }

            $this->splitMidnight($user, $lastUnfinishedAttendance, $now);

            return redirect()->route('dashboard')->with('message', $user->name . 'さん、お疲れさまでした！');
        } else {
            return redirect()->route('dashboard')->with('error', '勤務が開始されていません。');
        }
    }

    private function splitMidnight($user, $attendance, Carbon $now)
    {
        if ($attendance->work_date->toDateString() !== $now->toDateString()) {
            $attendance->end_time = $attendance->work_date->copy()->endOfDay();
        } else {
            $attendance->end_time = $now;
        }
        $attendance->save();

        if ($attendance->work_date->toDateString() !== $now->toDateString()) {

            $nextDayAttendance = new Attendance();
            $nextDayAttendance->user_id = $user->id;
            $nextDayAttendance->start_time = $now->copy()->startOfDay();
            $nextDayAttendance->end_time = $now;
            $nextDayAttendance->work_date = $now->toDateString();
            $nextDayAttendance->save();
        }
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
