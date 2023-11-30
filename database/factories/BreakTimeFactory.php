<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attendance = Attendance::inRandomOrder()->first();

        $startTime = $this->faker->dateTimeBetween($attendance->start_time, $attendance->end_time);

        $maxBreakDuration = min(2 * 60, Carbon::parse($attendance->start_time)->diffInMinutes(Carbon::parse($attendance->end_time)));
        $breakDuration = $this->faker->numberBetween(0, $maxBreakDuration);

        $endTime = Carbon::instance($startTime)->addMinutes($breakDuration);

        return [
            'attendance_id' => $attendance->id,
            'break_start_time' => $startTime,
            'break_end_time' => $endTime,
        ];
    }
}
