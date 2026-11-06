<?php

namespace App\Services\Platform;

use App\Enums\PlatformContactMessageStatus;
use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use App\Models\PlatformContactMessage;
use App\Models\PlatformContactMessageReply;
use App\Models\Property;
use App\Models\User;
use App\Notifications\NewPlatformContactMessageNotification;
use App\Notifications\PlatformContactReplyNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PlatformContactService
{
    /**
     * @param  array{name: string, email: string, phone?: string|null, subject: string, body: string, property_id?: int|null}  $data
     */
    public function submit(array $data, ?User $user = null): PlatformContactMessage
    {
        $propertyId = isset($data['property_id']) ? (int) $data['property_id'] : null;
        $organizationId = null;
        if ($propertyId) {
            $property = Property::query()->find($propertyId);
            $organizationId = $property?->organization_id;
        }

        $message = PlatformContactMessage::query()->create([
            'reference' => $this->generateReference(),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'user_id' => $user?->id,
            'organization_id' => $organizationId,
            'property_id' => $propertyId ?: null,
            'status' => PlatformContactMessageStatus::Pending,
        ]);

        $this->notifySuperAdmins($message);
        if ($organizationId !== null) {
            $this->notifyOrganizationOwners($message);
        }

        return $message;
    }

    public function reply(PlatformContactMessage $message, User $admin, string $body): PlatformContactMessageReply
    {
        $reply = PlatformContactMessageReply::query()->create([
            'contact_message_id' => $message->id,
            'user_id' => $admin->id,
            'body' => $body,
        ]);

        $message->update([
            'status' => PlatformContactMessageStatus::Replied,
        ]);

        Notification::route('mail', $message->email)
            ->notify(new PlatformContactReplyNotification($message, $reply));

        return $reply;
    }

    public function close(PlatformContactMessage $message): void
    {
        $message->update([
            'status' => PlatformContactMessageStatus::Closed,
            'closed_at' => now(),
        ]);
    }

    public function openInboxCount(): int
    {
        return PlatformContactMessage::query()
            ->whereIn('status', [
                PlatformContactMessageStatus::Pending,
                PlatformContactMessageStatus::Read,
                PlatformContactMessageStatus::Replied,
            ])
            ->count();
    }

    public function pendingCount(): int
    {
        return PlatformContactMessage::query()
            ->where('status', PlatformContactMessageStatus::Pending)
            ->count();
    }

    private function notifySuperAdmins(PlatformContactMessage $message): void
    {
        $admins = User::query()
            ->where('account_type', UserAccountType::SuperAdmin)
            ->where('status', UserStatus::Active)
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewPlatformContactMessageNotification($message));
        }
    }

    private function notifyOrganizationOwners(PlatformContactMessage $message): void
    {
        $orgId = $message->organization_id;
        if ($orgId === null) {
            return;
        }

        $owners = User::query()
            ->where('organization_id', $orgId)
            ->whereIn('account_type', [UserAccountType::OrganizationOwner, UserAccountType::Staff])
            ->where('status', UserStatus::Active)
            ->get();

        foreach ($owners as $owner) {
            $owner->notify(new NewPlatformContactMessageNotification($message));
        }
    }

    private function generateReference(): string
    {
        do {
            $reference = 'CNT-'.strtoupper(Str::random(8));
        } while (PlatformContactMessage::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
