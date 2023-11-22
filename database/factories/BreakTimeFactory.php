<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;

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

        // 勤務開始から勤務終了までのランダムな時間を生成
        $startTime = $this->faker->dateTimeBetween($attendance->start_time, $attendance->end_time);
        $endTime = $this->faker->dateTimeBetween($startTime, $attendance->end_time);

        return [
            'attendance_id' => $attendance->id,
            'break_start_time' => $startTime,
            'break_end_time' => $endTime,
        ];
    }
}
