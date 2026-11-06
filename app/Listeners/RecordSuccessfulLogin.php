<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Auth\Events\Login;

class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $request = request();

        $event->user->forceFill(['last_login_at' => now()])->save();

        LoginActivity::query()->create([
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'successful' => true,
            'logged_in_at' => now(),
        ]);
    }
}
