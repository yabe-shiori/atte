<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;

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
        $user = User::inRandomOrder()->first();

        // user_idを指定して作成
        // $user = User::find(91);

        $attendance = Attendance::inRandomOrder()->first() ?? Attendance::factory()->create(['user_id' => $user->id]);

        $startTime = $this->faker->dateTimeBetween($attendance->start_time, $attendance->end_time);

        // 休憩のデフォルト時間を1時間から2時間までのランダムな値に設定
        $breakDuration = $this->faker->numberBetween(60, 120);

        $endTime = Carbon::instance($startTime)->addMinutes($breakDuration);

        return [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'break_start_time' => $startTime,
            'break_end_time' => $endTime,
        ];
    }
}
