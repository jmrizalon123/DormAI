<?php

namespace App\Models;

use App\Enums\PlatformContactMessageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformContactMessage extends Model
{
    protected $fillable = [
        'reference',
        'name',
        'email',
        'phone',
        'subject',
        'body',
        'user_id',
        'organization_id',
        'property_id',
        'status',
        'read_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PlatformContactMessageStatus::class,
            'read_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(PlatformContactMessageReply::class, 'contact_message_id')->latest();
    }

    public function isOpen(): bool
    {
        return $this->status !== PlatformContactMessageStatus::Closed;
    }

    public function markRead(): void
    {
        if ($this->status === PlatformContactMessageStatus::Pending) {
            $this->update([
                'status' => PlatformContactMessageStatus::Read,
                'read_at' => now(),
            ]);

            return;
        }

        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }
}
