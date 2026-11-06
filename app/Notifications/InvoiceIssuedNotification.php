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

class InvoiceIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via(User $notifiable): array
    {
        return NotificationSettings::channelsFor($notifiable, $this->invoice->organization);
    }

    public function toMail(User $notifiable): MailMessage
    {
        $org = $this->invoice->organization->name;

        return (new MailMessage)
            ->subject("New invoice {$this->invoice->invoice_number} — {$org}")
            ->greeting('Hello '.$notifiable->name)
            ->line('A new invoice has been issued for your account.')
            ->line("Invoice: {$this->invoice->invoice_number}")
            ->line('Amount due: ₱'.number_format((float) $this->invoice->balance, 2))
            ->line('Due date: '.$this->invoice->due_date->format('M j, Y'))
            ->action('View invoice', NotificationRoutes::invoice($this->invoice, $notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'kind' => 'invoice_issued',
            'title' => 'New invoice',
            'message' => "{$this->invoice->invoice_number} — ₱".number_format((float) $this->invoice->balance, 2).' due '.$this->invoice->due_date->format('M j, Y'),
            'url' => NotificationRoutes::invoice($this->invoice, $notifiable),
            'organization_id' => $this->invoice->organization_id,
        ];
    }
}
