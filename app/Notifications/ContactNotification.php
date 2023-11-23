<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected string $name, protected string $email, protected string $content)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('【'.config('app.name').'】お問い合わせ')
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->cc(config('mail.admin.to'))
            ->greeting(__('名前：').$this->name)
            ->line('メッセージ：'.$this->content)
            ->line('メール：'.$this->email)
            ->salutation(__('このメールに返信はできないので問い合わせへの対応は新規メールを送信してください。'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
