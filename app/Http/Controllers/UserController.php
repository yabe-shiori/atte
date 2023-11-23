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
        // ユーザーに関連する勤怠データを取得
        $attendances = $user->attendance;

        // 勤怠データを月ごとにグループ化
        $attendancesByMonth = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->work_date)->format('Y-m');
        });

        // 月の選択ボックス用のデータを作成
        $months = $attendances->pluck('work_date')->unique()->map(function ($date) {
            return Carbon::parse($date)->format('Y-m');
        });

        // 選択された月があればその月のデータを、なければ最初の月のデータを表示
        $selectedMonth = $request->input('selectedMonth') ?? $months->first();

        // ビューにユーザーと月ごとの勤怠情報を渡す
        return view('profile.user-attendance', compact('user', 'attendancesByMonth', 'months', 'selectedMonth'));
    }
}

