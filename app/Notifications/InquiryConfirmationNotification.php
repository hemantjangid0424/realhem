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

        // Load property relation if not already loaded
        if ($this->inquiry->property_id && ! $this->inquiry->relationLoaded('property')) {
            $this->inquiry->load('property');
        }

        return (new MailMessage)
            ->subject("Confirmation: {$typeLabel} Received • RealHem")
            ->view('emails.inquiries.buyer-confirmation', [
                'inquiry' => $this->inquiry,
                'typeLabel' => $typeLabel,
            ]);
    }
}
