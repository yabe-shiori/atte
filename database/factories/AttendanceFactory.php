<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;


    //過去一年間のデータを作成

    // public function definition()
    // {
    //     $user = User::inRandomOrder()->first();

    //     $startTime = $this->faker->dateTimeBetween('-1 year', 'now');
    //     $endTime = $this->faker->dateTimeInInterval($startTime, '+1 day');
    //     $endTime = $endTime > now() ? now() : $endTime;

    //     $endOfDay = now()->endOfDay();

    //     return [
    //         'user_id' => $user->id,
    //         'work_date' => $startTime->format('Y-m-d'),
    //         'start_time' => $startTime,
    //         'end_time' => $endTime,
    //         'crossed_midnight' => $endTime > $endOfDay,
    //     ];
    // }


    //過去一週間のデータを作成
    public function definition()
    {
        $user = User::inRandomOrder()->first();

        // 最近一週間の範囲からランダムに日付を選択
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        $startTime = $this->faker->dateTimeBetween($startDate, $endDate);
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
