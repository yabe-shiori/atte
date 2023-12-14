<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use App\Http\Controllers\AttendanceController;


class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_view()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_start_work()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('start-work'));
        $response->assertRedirect(route('dashboard'));

        $attendance = Attendance::factory()->create(['user_id' => $user->id, 'start_time' => now()]);
        $response = $this->actingAs($user)->post(route('start-work'));
        $response->assertRedirect(route('dashboard'))->assertSessionHas('error');
    }

    public function test_end_work()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('end-work'));
        $response->assertRedirect(route('dashboard'));

        $response = $this->actingAs($user)->post(route('end-work'));
        $response->assertRedirect(route('dashboard'))->assertSessionHas('error');
    }

    public function test_attendance_list_view()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance-list'));
        $response->assertStatus(200);
    }
}
