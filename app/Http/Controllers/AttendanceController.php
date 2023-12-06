<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Log;

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

            // 出勤時刻が深夜をまたいでいる場合、分割して保存
            if ($attendance->crossed_midnight) {
                // 勤怠レコードの日付を変更し、出勤時刻から深夜までを保存
                $attendance->work_date = now()->toDateString();
                $attendance->end_time = now()->setTime(24, 0, 0);
                $attendance->save();

                // 新しいレコードを作成して、深夜から退勤時刻までを保存
                $nextDayAttendance = new Attendance();
                $nextDayAttendance->user_id = $user->id;
                $nextDayAttendance->start_time = now()->setTime(0, 0, 0);

                // ここでユーザーが勤務終了ボタンを押した時刻をセット
                $nextDayAttendance->end_time = now();
                $nextDayAttendance->work_date = now()->addDay()->toDateString();
                $nextDayAttendance->save();

                // 勤務終了ボタンが押されていないか確認
                $this->checkAutomaticEndTime($attendance);
                Log::info('Job dispatched successfully.');

                return redirect()->route('dashboard')->with('message', '出勤しました！');
            } else {

                $attendance->work_date = now()->toDateString();
                $attendance->save();

                $this->checkAutomaticEndTime($attendance);

                return redirect()->route('dashboard')->with('message', '出勤しました！');
            }
        }

        return redirect()->route('dashboard')->with('error', '本日の勤務は既に開始しています。');
    }

    public function checkAutomaticEndTime($attendance)
    {
        $user = Auth::user();

        // 勤務終了ボタンが押されていない場合かつ開始から10時間以上経過している場合
        if (is_null($attendance->end_time) && now()->diffInHours($attendance->start_time) >= 1) {
            $attendance->update([
                'end_time' => $attendance->start_time->addHours(1),
            ]);

            Log::info('ジョブディスパッチ前');
            // SetEndWorkTimeJob::dispatch($user, $attendance)->delay(now()->addHours(10))->onQueue('end_work');
            Log::info('ジョブディスパッチ後');
        }
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
