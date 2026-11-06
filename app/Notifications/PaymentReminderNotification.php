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

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public int $daysUntilDue,
    ) {}

    public function via(User $notifiable): array
    {
        return NotificationSettings::channelsFor($notifiable, $this->invoice->organization);
    }

    public function toMail(User $notifiable): MailMessage
    {
        $when = $this->daysUntilDue === 0
            ? 'today'
            : "in {$this->daysUntilDue} day(s)";

        return (new MailMessage)
            ->subject("Payment reminder — {$this->invoice->invoice_number}")
            ->line("Your invoice {$this->invoice->invoice_number} is due {$when}.")
            ->line('Balance: ₱'.number_format((float) $this->invoice->balance, 2))
            ->action('View invoice', NotificationRoutes::invoice($this->invoice, $notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        $when = $this->daysUntilDue === 0
            ? 'due today'
            : "due in {$this->daysUntilDue} day(s)";

        return [
            'kind' => 'payment_reminder',
            'title' => 'Payment reminder',
            'message' => "{$this->invoice->invoice_number} {$when} — ₱".number_format((float) $this->invoice->balance, 2),
            'url' => NotificationRoutes::invoice($this->invoice, $notifiable),
            'organization_id' => $this->invoice->organization_id,
        ];
    }
}
