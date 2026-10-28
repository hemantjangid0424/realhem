<?php

namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ListingPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(public Property $property) {}

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
        $ownerName = $notifiable->name ?? $this->property->user?->name ?? 'Property Owner';
        $refId = $this->property->id ?? 'LIVE';

        return (new MailMessage)
            ->subject("Congratulations! Your Listing is Live on RealHem • #PROP-{$refId}")
            ->view('emails.properties.listing-published', [
                'ownerName' => $ownerName,
                'property' => $this->property,
            ]);
    }
}

