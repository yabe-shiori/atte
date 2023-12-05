<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceService;
use App\Jobs\SetEndWorkTimeJob;


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

        $todayAttendance = $user->attendance()->whereDate('work_date', now()->toDateString())->first();
        if (!$todayAttendance) {
            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->start_time = now();

            $attendance->crossed_midnight = $this->hasCrossedMidnight($user);

            if ($attendance->crossed_midnight) {
                if (!$this->hasPreviousDayEndButtonPressed($user)) {
                    // ジョブをディスパッチして非同期で勤務終了時刻を設定
                    SetEndWorkTimeJob::dispatch($user, $attendance)
                        ->delay(now()->addHours(10))
                        ->onQueue('end_work'); // キュー名を指定

                    return redirect()->route('dashboard')->with('message', '前日の勤務終了ボタンが押されていません。正しい勤務終了時刻を管理者にお伝えください。');
                }
            }

            $attendance->work_date = now()->toDateString();
            $attendance->save();

            return redirect()->route('dashboard')->with('message', '出勤しました！');
        }

        return redirect()->route('dashboard')->with('error', '本日の勤務は既に開始しています。');
    }


    private function hasPreviousDayEndButtonPressed($user)
    {
        return $user->attendance()
            ->whereDate('end_time', now()->subDay()->toDateString())
            ->exists();
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

    private function hasCrossedMidnight($user)
    {
        return $user->attendance()
            ->where(function ($query) {
                $query->where('start_time', '<', '06:00:00')
                    ->orWhere(function ($query) {
                        $query->where('end_time', '>=', '22:00:00')
                            ->orWhereNull('end_time');
                    });
            })
            ->exists();
    }
}
