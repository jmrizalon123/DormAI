<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Models\User;
use App\Support\Notifications\NotificationRoutes;
use App\Support\Notifications\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Contract $contract,
        public int $daysUntilEnd,
    ) {}

    public function via(User $notifiable): array
    {
        return NotificationSettings::channelsFor($notifiable, $this->contract->organization);
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Rental agreement expiring — {$this->contract->contract_number}")
            ->line("Your rental agreement expires in {$this->daysUntilEnd} day(s).")
            ->line('End date: '.$this->contract->end_date?->format('M j, Y'))
            ->action('View agreement', NotificationRoutes::contract($this->contract, $notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'kind' => 'contract_expiring',
            'title' => 'Agreement expiring',
            'message' => "{$this->contract->contract_number} ends in {$this->daysUntilEnd} day(s)",
            'url' => NotificationRoutes::contract($this->contract, $notifiable),
            'organization_id' => $this->contract->organization_id,
        ];
    }
}
