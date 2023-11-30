<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use App\Models\User;


class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'break_start_time',
        'break_end_time',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
