<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
use App\Models\BreakTime;


class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'crossed_midnight',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }
    public function calculateWorkDuration()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $end->diff($start)->format('%H:%I:%S');
    }

    public function calculateBreakDuration()
    {
        return app(\App\Services\AttendanceService::class)->calculateBreakDuration($this);
    }

    public function calculateWorkTime()
    {
        return app(\App\Services\AttendanceService::class)->calculateWorkTime($this);
    }
}
