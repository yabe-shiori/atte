<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BreakTimeController;
use App\Http\Controllers\UserController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    //打刻画面
    Route::get('/', [AttendanceController::class, 'index'])
        ->name('dashboard');
    //出勤
    Route::post('/start-work', [AttendanceController::class, 'startWork'])
        ->name('start-work');
    //退勤
    Route::post('/end-work', [AttendanceController::class, 'endWork'])
        ->name('end-work');
    //休憩開始
    Route::post('/start-break', [BreakTimeController::class, 'startBreak'])
        ->name('start-break');
    //休憩終了
    Route::post('/end-break', [BreakTimeController::class, 'endBreak'])
        ->name('end-break');
    // 休憩時間保存処理
    Route::post('/break-time/store', [BreakTimeController::class, 'store'])
        ->name('break-time.store');
    // 勤怠一覧画面
    Route::get('/attendance', [AttendanceController::class, 'attendanceList'])
        ->name('attendance-list');
    //自分のプロフィールの編集更新
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    //自分の勤怠情報を表示
    Route::get('mypage/attendance/{user}', [UserController::class, 'myAttendance'])
        ->name('my-attendance');
});


//管理者のみアクセス可能
Route::middleware(['auth', 'can:admin'])->group(function () {
    // ユーザー一覧画面
    Route::get('admin/index', [ProfileController::class, 'index'])
        ->name('admin.index');
    //ユーザー情報編集
    Route::get('/profile/adedit/{user}', [ProfileController::class, 'adedit'])
        ->name('profile.adedit');
    Route::patch('/profile/adupdate/{user}', [ProfileController::class, 'adupdate'])
        ->name('profile.adupdate');
    //役割付与
    Route::patch('roles/{user}/attach', [RoleController::class, 'attach'])
        ->name('role.attach');
    //役割削除
    Route::patch('roles/{user}/detach', [RoleController::class, 'detach'])
        ->name('role.detach');
    //ユーザーごとの勤怠表示
    Route::get('admin/user-attendance/{user}', [UserController::class, 'index'])
        ->name('user-attendance');
});

require __DIR__ . '/auth.php';
