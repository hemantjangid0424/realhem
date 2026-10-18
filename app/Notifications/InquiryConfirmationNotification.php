<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InquiryConfirmationNotification extends Notification
{
    use Queueable;

    public function __construct(public Inquiry $inquiry) {}

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
        $typeLabel = match ($this->inquiry->type) {
            'request_info' => 'Property Tour & Info Reservation',
            'feedback' => 'Feedback Submission',
            'report_problem' => 'Support & Issue Ticket',
            'career' => 'Job Application',
            default => 'Property Inquiry & Visit Reservation',
        };

        return (new MailMessage)
            ->subject("Confirmation: {$typeLabel} Received • RealHem")
            ->greeting("Hello {$this->inquiry->name}!")
            ->line("Thank you for submitting your {$typeLabel} with RealHem.")
            ->line('Your request has been registered in our system and assigned to a verified property specialist.')
            ->line("Message Details: \"{$this->inquiry->message}\"")
            ->line('Our team will review your request and get back to you shortly.')
            ->salutation("Best regards,\nThe RealHem Team");
    }
}
