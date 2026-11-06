<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\User;
use App\Support\Notifications\NotificationRoutes;
use App\Support\Notifications\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via(User $notifiable): array
    {
        return NotificationSettings::channelsFor($notifiable, $this->invoice->organization);
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Overdue invoice — {$this->invoice->invoice_number}")
            ->line('Your invoice is now overdue.')
            ->line('Balance: ₱'.number_format((float) $this->invoice->balance, 2))
            ->action('View invoice', NotificationRoutes::invoice($this->invoice, $notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'kind' => 'invoice_overdue',
            'title' => 'Invoice overdue',
            'message' => "{$this->invoice->invoice_number} — ₱".number_format((float) $this->invoice->balance, 2).' overdue',
            'url' => NotificationRoutes::invoice($this->invoice, $notifiable),
            'organization_id' => $this->invoice->organization_id,
        ];
    }
}
