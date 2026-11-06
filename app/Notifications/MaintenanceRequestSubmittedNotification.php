<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Support\Notifications\NotificationRoutes;
use App\Support\Notifications\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MaintenanceRequest $maintenanceRequest) {}

    public function via(User $notifiable): array
    {
        return NotificationSettings::channelsFor($notifiable, $this->maintenanceRequest->organization);
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New maintenance request — {$this->maintenanceRequest->reference_code}")
            ->line($this->maintenanceRequest->title)
            ->action('Review request', NotificationRoutes::maintenanceRequest($this->maintenanceRequest, $notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'kind' => 'maintenance_submitted',
            'title' => 'New maintenance request',
            'message' => "{$this->maintenanceRequest->reference_code}: {$this->maintenanceRequest->title}",
            'url' => NotificationRoutes::maintenanceRequest($this->maintenanceRequest, $notifiable),
            'organization_id' => $this->maintenanceRequest->organization_id,
        ];
    }
}
