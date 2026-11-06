<?php

namespace App\Notifications;

use App\Models\Reservation;
use App\Models\User;
use App\Support\Notifications\NotificationRoutes;
use App\Support\Notifications\NotificationSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationSubmittedNotification extends Notification
{
    public function __construct(public Reservation $reservation) {}

    public function via(User $notifiable): array
    {
        return NotificationSettings::channelsFor($notifiable, $this->reservation->organization);
    }

    public function toMail(User $notifiable): MailMessage
    {
        $this->reservation->loadMissing(['property', 'room', 'bedSpace']);

        $property = $this->reservation->property?->name ?? 'Property';
        $line = $this->reservation->applicantFullName().' requested a bed at '.$property;

        return (new MailMessage)
            ->subject("New reservation request — {$this->reservation->reference_code}")
            ->line($line)
            ->action('Review reservation', NotificationRoutes::reservation($this->reservation, $notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        $this->reservation->loadMissing(['property']);

        return [
            'kind' => 'reservation_submitted',
            'title' => 'New reservation request',
            'message' => "{$this->reservation->reference_code}: {$this->reservation->applicantFullName()} · ".($this->reservation->property?->name ?? 'Listing'),
            'url' => NotificationRoutes::reservation($this->reservation, $notifiable),
            'organization_id' => $this->reservation->organization_id,
        ];
    }
}
