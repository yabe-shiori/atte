<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminUserController extends Controller
{
    public function editAttendance(User $user, Attendance $attendance)
    {
        return view('admin.edit-attendance', compact('user', 'attendance'));
    }

    public function updateAttendance(Request $request, User $user, Attendance $attendance)
    {
        // 入力検証やデータの更新などを行う
        $request->validate([
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s',
        ]);

        // 勤怠情報を更新
        $attendance->start_time = Carbon::parse($request->input('start_time'));
        $attendance->end_time = Carbon::parse($request->input('end_time'));
        $attendance->save();

        return redirect()->route('user-attendance', ['user' => $user->id])->with('message', '勤怠情報が更新されました');
    }
}
