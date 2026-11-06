<?php

namespace App\Notifications;

use App\Models\PlatformContactMessage;
use App\Models\PlatformContactMessageReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformContactReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PlatformContactMessage $contactMessage,
        public PlatformContactMessageReply $reply,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Re: {$this->contactMessage->subject} — {$this->contactMessage->reference}")
            ->greeting("Hello {$this->contactMessage->name},")
            ->line('The '.config('app.name').' team replied to your contact message:')
            ->line($this->reply->body)
            ->action('View conversation', route('public.contact.show', [
                'reference' => $this->contactMessage->reference,
                'email' => $this->contactMessage->email,
            ]))
            ->line('You can reply again from the marketplace contact page if you need more help.');
    }
}
