<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EndWorkTimeSetNotification extends Notification
{
    use Queueable;

    protected $attendance;

    public function __construct($attendance)
    {
        $this->attendance = $attendance;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('勤務終了時刻が自動で設定されました。')
            ->line('設定された時刻: ' . $this->attendance->end_time)
            ->line('次回の出勤日に正しい勤務終了時刻を管理者にお伝えください。')
            ->action('アプリケーションにアクセス', url('/'))
            ->line('アプリケーションをご利用いただきありがとうございます。');
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
