<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BreakTimeController;
use App\Http\Controllers\UserController;




Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])
        ->name('dashboard');

    Route::post('/start-work', [AttendanceController::class, 'startWork'])
        ->name('start-work');

    Route::post('/end-work', [AttendanceController::class, 'endWork'])
        ->name('end-work');

    Route::post('/start-break', [BreakTimeController::class, 'startBreak'])
        ->name('start-break');

    Route::post('/end-break', [BreakTimeController::class, 'endBreak'])
        ->name('end-break');

    Route::post('/break-time/store', [BreakTimeController::class, 'store'])
        ->name('break-time.store');

    Route::get('/attendance', [AttendanceController::class, 'attendanceList'])
        ->name('attendance-list');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::get('mypage/attendance/{user}', [UserController::class, 'myAttendance'])
        ->name('my-attendance');
});

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('admin/index', [ProfileController::class, 'index'])
        ->name('admin.index');

    Route::get('/profile/adedit/{user}', [ProfileController::class, 'adedit'])
        ->name('profile.adedit');

    Route::patch('/profile/adupdate/{user}', [ProfileController::class, 'adupdate'])
        ->name('profile.adupdate');

    Route::patch('roles/{user}/attach', [RoleController::class, 'attach'])
        ->name('role.attach');

    Route::patch('roles/{user}/detach', [RoleController::class, 'detach'])
        ->name('role.detach');
        
    Route::get('admin/user-attendance/{user}', [UserController::class, 'index'])
        ->name('user-attendance');
});

require __DIR__ . '/auth.php';
