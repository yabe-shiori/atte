<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;


    public function definition()
    {
        $user = User::inRandomOrder()->first();

        $startTime = $this->faker->dateTimeBetween('-1 year', 'now');
        $endTime = $this->faker->dateTimeInInterval($startTime, '+1 day');
        $endTime = $endTime > now() ? now() : $endTime;

        $endOfDay = now()->endOfDay();

        return [
            'user_id' => $user->id,
            'work_date' => $startTime->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'crossed_midnight' => $endTime > $endOfDay,
        ];
    }
}
