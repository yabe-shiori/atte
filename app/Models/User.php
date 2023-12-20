<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\NewVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\BreakTime;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }


    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new NewVerifyEmail());
    }

    public function hasStartedWorkOnDate($date)
    {
        return $this->attendance()->whereDate('start_time', $date)->exists();
    }

    public function hasEndedWorkOnDate($date)
    {
        return $this->attendance()->whereDate('end_time', $date)->exists();
    }
}
