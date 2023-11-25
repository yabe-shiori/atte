<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Services\AttendanceService;

class UserController extends Controller
{

    public function index(User $user, Request $request)
    {
        $attendances = $user->attendance;
        list($attendancesByMonth, $months, $selectedMonth) = $this->processAttendanceData($attendances, $request);

        return view('admin.user-attendance', compact('user', 'attendancesByMonth', 'months', 'selectedMonth'));
    }

    public function myAttendance(User $user, Request $request)
    {
        $attendances = $user->attendance;
        list($attendancesByMonth, $months, $selectedMonth) = $this->processAttendanceData($attendances, $request);

        return view('mypage.my-attendance', compact('user', 'attendancesByMonth', 'months', 'selectedMonth'));
    }

    private function processAttendanceData($attendances, $request)
    {
        $attendancesByMonth = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->work_date)->format('Y-m');
        });

        $months = $attendances->pluck('work_date')->unique()->map(function ($date) {
            return Carbon::parse($date)->format('Y-m');
        });

        // 年月を新しい順にソート
        $months = $months->sort(function ($a, $b) {
            return Carbon::parse($b)->getTimestamp() - Carbon::parse($a)->getTimestamp();
        });

        // 最新の月を取得
        $latestMonth = $months->first();

        // 選択された月があればその月のデータを、なければ最新の月のデータを表示
        $selectedMonth = $request->input('selectedMonth', $latestMonth);

        return [$attendancesByMonth, $months, $selectedMonth];
    }
}

