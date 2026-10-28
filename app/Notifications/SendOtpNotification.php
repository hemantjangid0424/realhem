<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $otp,
        public ?string $userName = null,
        public int $expiresInMinutes = 10
    ) {}

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
        $name = $this->userName ?: ($notifiable->name ?? 'Valued User');

        return (new MailMessage)
            ->subject("Your RealHem Verification Code: {$this->otp}")
            ->view('emails.auth.otp', [
                'otp' => $this->otp,
                'userName' => $name,
                'expiresInMinutes' => $this->expiresInMinutes,
            ]);
    }
}
