<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformContactMessageReply extends Model
{
    protected $table = 'platform_contact_replies';

    protected $fillable = [
        'contact_message_id',
        'user_id',
        'body',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(PlatformContactMessage::class, 'contact_message_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
