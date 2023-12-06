<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EndWorkTimeSetNotification;
use Illuminate\Support\Facades\Log;


class SetEndWorkTimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $attendance;

    public function __construct($user, $attendance)
    {
        $this->user = $user;
        $this->attendance = $attendance;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // ログを追加
        Log::info('SetEndWorkTimeJob is processing.');
        Log::info('User: ' . $this->user->name);
        Log::info('Attendance ID: ' . $this->attendance->id);


        // ユーザーがボタンを押していない場合にのみ勤務終了時刻を設定
        if (is_null($this->attendance->end_time)) {
            $this->attendance->update([
                'end_time' => $this->attendance->start_time->addHours(1),
            ]);

            //ログの追加
            Log::info('End time has been set automatically.');

            // 非同期通知を送信
            //メール通知機能を一時的にコメントアウトしています。
            // Notification::send($this->user, new EndWorkTimeSetNotification($this->attendance));
        }
    }
}
