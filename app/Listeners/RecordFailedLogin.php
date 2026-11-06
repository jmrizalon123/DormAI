<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use Illuminate\Auth\Events\Failed;

class RecordFailedLogin
{
    public function handle(Failed $event): void
    {
        $request = request();

        LoginActivity::query()->create([
            'user_id' => $event->user?->id,
            'email' => $event->credentials['email'] ?? null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'successful' => false,
            'failure_reason' => 'invalid_credentials',
            'logged_in_at' => now(),
        ]);
    }
}
