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

        $allMonths = collect();
        $startDate = Carbon::now()->subYear();
        while ($startDate->lte(now())) {
            $allMonths->push($startDate->format('Y-m'));
            $startDate->addMonth();
        }

        $allMonths = $allMonths->reverse();

        $selectedMonth = $request->input('selectedMonth', $allMonths->first());

        return [$attendancesByMonth, $allMonths, $selectedMonth];
    }
}
