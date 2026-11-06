<?php

namespace App\Notifications;

use App\Models\PlatformContactMessage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewPlatformContactMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PlatformContactMessage $contactMessage) {}

    public function via(User $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $this->contactMessage->loadMissing(['property:id,name', 'organization:id,name']);

        $isListingInquiry = $this->contactMessage->property_id !== null;
        $subjectPrefix = $isListingInquiry ? 'New listing inquiry' : 'New contact message';

        $mail = (new MailMessage)
            ->subject("{$subjectPrefix} — {$this->contactMessage->reference}")
            ->greeting($isListingInquiry ? 'Someone messaged about your listing' : 'New marketplace contact')
            ->line("From: {$this->contactMessage->name} ({$this->contactMessage->email})");

        if ($this->contactMessage->property) {
            $mail->line("Listing: {$this->contactMessage->property->name}");
        }

        $mail->line("Subject: {$this->contactMessage->subject}")
            ->line(Str::limit($this->contactMessage->body, 200))
            ->action('Open in inbox', $this->panelUrlFor($notifiable));

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        $this->contactMessage->loadMissing(['property:id,name']);

        $title = $this->contactMessage->property_id !== null
            ? 'New listing inquiry'
            : 'New contact message';

        $preview = $this->contactMessage->property
            ? "{$this->contactMessage->property->name}: {$this->contactMessage->subject}"
            : "{$this->contactMessage->reference}: {$this->contactMessage->subject}";

        return [
            'kind' => 'platform_contact_new',
            'title' => $title,
            'message' => $preview,
            'url' => $this->panelUrlFor($notifiable),
        ];
    }

    private function panelUrlFor(User $notifiable): string
    {
        if (
            $this->contactMessage->organization_id !== null
            && $notifiable->organization_id !== null
            && (int) $this->contactMessage->organization_id === (int) $notifiable->organization_id
        ) {
            return route('organization.contact-messages.show', $this->contactMessage);
        }

        return route('platform.contact-messages.show', $this->contactMessage);
    }
}
