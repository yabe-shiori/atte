<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;


class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start_time',
        'break_end_time',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

}

