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

        // 過去の最新の出勤データを取得
        $latestAttendance = $user->attendance()
            ->latest('work_date')
            ->first();

        $workStarted = $latestAttendance ? !is_null($latestAttendance->start_time) : false;
        $workEnded = $latestAttendance ? !is_null($latestAttendance->end_time) : false;

        return view('dashboard', [
            'user' => $user,
            'latestAttendance' => $latestAttendance,
            'workStarted' => $workStarted,
            'workEnded' => $workEnded,
        ]);
    }
    public function startWork()
    {
        $user = Auth::user();

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

            $attendance->end_time = null;
        }

        $attendance->work_date = now()->toDateString();
        $attendance->save();

        // $userModel = \App\Models\User::find($user->id);
        // // $userModel->update(['work_started' => true]);

        return redirect()->route('dashboard')->with('message', '出勤しました！');
    }

    public function endWork()
    {
        $user = Auth::user();

        $todayAttendance = $user->attendance()
            ->whereDate('work_date', now()->toDateString())
            ->whereNull('end_time')
            ->first();

        //休憩が終了されているか
        if ($todayAttendance) {

            $breaks = $todayAttendance->breakTimes;
            foreach ($breaks as $break) {
                if (is_null($break->break_end_time)) {
                    return redirect()->route('dashboard')->with('error', '休憩が終了していません。');
                }
            }

            $todayAttendance->end_time = now();
            $todayAttendance->save();
            $message = $user->name . 'さん、お疲れさまでした！';

            // $userModel = \App\Models\User::find($user->id);
            // $userModel->update(['work_started' => false]);

            return redirect()->route('dashboard')->with('message', $message);
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
}
