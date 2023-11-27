<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;
    protected $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendanceService = new AttendanceService();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testCalculateWorkDuration()
    {
        $attendance = (object) [
            'start_time' => '2023-01-01 09:00:00',
            'end_time' => '2023-01-01 18:00:00',
        ];

        $result = $this->attendanceService->calculateWorkDuration($attendance);
        $this->assertEquals('09:00:00', $result);
    }

    public function testCalculateBreakDuration()
    {
        $attendance = (object) [
            'breakTimes' => new Collection([
                (object) ['break_start_time' => '2023-01-01 12:00:00', 'break_end_time' => '2023-01-01 13:00:00'],
            ]),
        ];

        $result = $this->attendanceService->calculateBreakDuration($attendance);
        $this->assertEquals('01:00:00', $result);
    }

    public function testCalculateWorkTime()
    {
        $attendance = (object) [
            'start_time' => '2023-01-01 09:00:00',
            'end_time' => '2023-01-01 18:00:00',
            'breakTimes' => new Collection([
                (object) ['break_start_time' => '2023-01-01 12:00:00', 'break_end_time' => '2023-01-01 13:00:00'],
            ]),
        ];

        $result = $this->attendanceService->calculateWorkTime($attendance);
        $this->assertEquals('08:00:00', $result);
    }

    public function testParseDuration()
    {
        $result = $this->app->call([$this->attendanceService, 'parseDuration'], ['duration' => '01:30:00']);
        $this->assertEquals(5400, $result);
    }

    public function testFormatDuration()
    {
        $result = $this->app->call([$this->attendanceService, 'formatDuration'], ['seconds' => 5400]);
        $this->assertEquals('01:30:00', $result);
    }
    public function testGetAttendancesByDate()
    {
        // Create test data
        $user1 = User::factory()->create(['name' => 'User 1']);
        $user2 = User::factory()->create(['name' => 'User 2']);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2023-01-01',
            'start_time' => '2023-01-01 09:00:00',
            'end_time' => '2023-01-01 18:00:00',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => '2023-01-01',
            'start_time' => '2023-01-01 10:00:00',
            'end_time' => '2023-01-01 17:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance1->id,
            'break_start_time' => '2023-01-01 12:00:00',
            'break_end_time' => '2023-01-01 13:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance2->id,
            'break_start_time' => '2023-01-01 14:00:00',
            'break_end_time' => '2023-01-01 15:00:00',
        ]);

        $result = $this->attendanceService->getAttendancesByDate('2023-01-01');

        $this->assertCount(2, $result);

        $this->assertEquals('User 1', $result[0]->name);
        $this->assertEquals('2023-01-01', $result[0]->work_date);

        $this->assertEquals('User 2', $result[1]->name);
        $this->assertEquals('2023-01-01', $result[1]->work_date);
    }
}
