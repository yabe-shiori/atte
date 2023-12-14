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

            $crossedMidnight = $this->hasCrossedMidnight($user, $now);

            if ($crossedMidnight) {
                $this->splitMidnight($user, $todayAttendance, $now);
            } else {
                if ($todayAttendance->end_time === null) {
                    $todayAttendance->end_time = $now;
                    $todayAttendance->save();
                }
            }

            return redirect()->route('dashboard')->with('message', $user->name . 'さん、お疲れさまでした！');
        }

        return redirect()->route('dashboard')->with('error', '勤務が開始されていません。');
    }

    private function splitMidnight($user, $attendance, Carbon $now)
    {
        // 日をまたいでいる場合のみ処理を行う
        if ($this->hasCrossedMidnight($user, $now)) {
            // 勤務終了時間をその日の最後の時刻である 23:59:59 に設定
            $attendance->end_time = $now->copy()->endOfDay();
            $attendance->save();

            // 翌日のための新しいレコードを作成
            $nextDayAttendance = new Attendance();
            $nextDayAttendance->user_id = $user->id;
            $nextDayAttendance->start_time = $now->copy()->startOfDay();
            $nextDayAttendance->work_date = $now->copy()->addDay()->toDateString(); // 翌日の日付をセット
            // 翌日の終了時刻はまだ設定しない
            $nextDayAttendance->save();
        } else {
            // 日をまたいでいない場合は、現在の時刻を終了時刻として設定
            $attendance->end_time = $now;
            $attendance->save();
        }
    }

    private function hasCrossedMidnight($user, Carbon $now)
    {
        // ユーザーが昨日働き始めて、今日もまだ働いているかどうかを確認
        $attendanceYesterday = $user->attendance()
            ->whereDate('work_date', $now->copy()->subDay()->toDateString())
            ->orderBy('start_time', 'desc')
            ->first();

        return $attendanceYesterday && $attendanceYesterday->end_time == null;
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
