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
    //     //ランダムで作成
    //     $user = User::inRandomOrder()->first();

    //     // ユーザーIDを指定して作成
    //     // $user = User::find(91);

    //     $startDate = Carbon::now()->subYear();
    //     $endDate = Carbon::now();

    //     $startTime = $this->faker->dateTimeBetween($startDate, $endDate);
    //     $endTime = $this->faker->dateTimeBetween(
    //         $startTime,
    //         Carbon::instance($startTime)->endOfDay()->subMinutes(1)->getTimestamp()
    //     );

    //     $endOfDay = now()->endOfDay();

    //     return [
    //         'user_id' => $user->id,
    //         'work_date' => $startTime->format('Y-m-d'),
    //         'start_time' => $startTime,
    //         'end_time' => $endTime,
    //         'crossed_midnight' => $endTime > $endOfDay,
    //     ];
    // }

    // //過去一週間のデータを作成
    public function definition()
    {
        $user = User::inRandomOrder()->first();

        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        $startTime = $this->faker->dateTimeBetween($startDate, $endDate);
        $endTime = $this->faker->dateTimeBetween(
            $startTime,
            Carbon::instance($startTime)->endOfDay()->subMinutes(1)->getTimestamp()
        );

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
