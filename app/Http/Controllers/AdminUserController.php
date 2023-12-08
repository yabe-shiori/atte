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
        $request->validate([
            'work_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $attendance->work_date = Carbon::parse($request->input('work_date'));
        $attendance->start_time = Carbon::parse($request->input('start_time'));
        $attendance->end_time = Carbon::parse($request->input('end_time'));
        $attendance->save();

        return redirect()->route('user-attendance', ['user' => $user->id])->with('message', '勤怠情報が更新されました');
    }
}
