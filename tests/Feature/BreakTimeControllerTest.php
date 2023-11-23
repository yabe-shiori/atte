<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Http\Controllers\BreakTimeController;
use Carbon\Carbon;

class BreakTimeControllerTest extends TestCase
{
    use RefreshDatabase;

    // public function testStoreBreakTime()
    // {
    //     $user = User::factory()->create();
    //     $attendance = Attendance::factory()->create([
    //         'user_id' => $user->id,
    //         'start_time' => now(),
    //         'end_time' => null,
    //     ]);

    //     $this->actingAs($user);

    //     $response = $this->post(route('break-time.store'), [
    //         'attendance_id' => $attendance->id,
    //         'break_start_time' => now(),
    //         'break_end_time' => now()->addHour(),
    //     ]);

    //     $response->assertRedirect(route('dashboard'));

    //     // テストで休憩が正常に保存されたかを確認
    //     $this->assertDatabaseHas('break_times', [
    //         'attendance_id' => $attendance->id,
    //         'break_start_time' => now(),
    //         'break_end_time' => now()->addHour(),
    //     ]);
    // }
    // public function testStartBreak()
    // {
    //     $user = User::factory()->create();
    //     $attendance = Attendance::factory()->create([
    //         'user_id' => $user->id,
    //         'start_time' => now(),
    //         'end_time' => null,
    //     ]);
    //     $this->actingAs($user);

    //     $this->assertNull(session('break_started'));
    //     $response = $this->post(route('start-break'));
    //     $response->assertRedirect(route('dashboard'));

    //     dump(session('message'));
    //     dump('休憩を開始しました。');

    //     $this->assertEquals(session('message'), '休憩を開始しました。');

    //     $this->assertTrue(session('break_started'));
    //     $this->assertDatabaseHas('break_times', [
    //         'attendance_id' => $attendance->id,
    //         'break_start_time' => now(),
    //     ]);
    // }

    // public function testEndBreak()
    // {
    //     $user = User::factory()->create();
    //     $attendance = Attendance::factory()->create([
    //         'user_id' => $user->id,
    //         'start_time' => now(),
    //         'end_time' => null,
    //     ]);

    //     BreakTime::factory()->create([
    //         'attendance_id' => $attendance->id,
    //         'break_start_time' => now()->subHour(),
    //         'break_end_time' => null,
    //     ]);

    //     $this->actingAs($user);

    //     session(['break_started' => true]);

    //     $response = $this->post(route('end-break'));

    //     $response->assertRedirect(route('dashboard'))->assertSessionHas('message', '休憩を終了しました。');

    //     $this->assertFalse(session('break_started'));

    //     $this->assertDatabaseHas('break_times', [
    //         'attendance_id' => $attendance->id,
    //         'break_start_time' => now()->subHour(),
    //         'break_end_time' => now(),
    //     ]);
    // }
    // public function testGetTodayAttendance()
    // {
    //     $user = User::factory()->create();

    //     $this->actingAs($user);

    //     $attendance = Attendance::factory()->create([
    //         'user_id' => $user->id,
    //         'start_time' => now()->toDateString(),
    //     ]);

    //     $result = $this->callMethod(new BreakTimeController(), 'getTodayAttendance');

    //     $this->assertEquals($attendance->id, $result->id);
    // }

    // protected function callMethod($object, $method, $parameters = [])
    // {
    //     $method = new \ReflectionMethod(get_class($object), $method);
    //     $method->setAccessible(true);

    //     return $method->invokeArgs($object, $parameters);
    // }
    
}

