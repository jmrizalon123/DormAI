<?php

namespace App\Services\Portal;

use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TenantPortalAccessService
{
    public function enable(Tenant $tenant, string $email, string $password, ?string $name = null): User
    {
        $email = strtolower(trim($email));

        if ($tenant->user_id !== null) {
            throw ValidationException::withMessages([
                'email' => ['This tenant already has portal access.'],
            ]);
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser !== null) {
            if ($existingUser->isOrganizationOwner() || $existingUser->can('view organization dashboard')) {
                throw ValidationException::withMessages([
                    'email' => ['That email belongs to a staff account. Use a different tenant login email.'],
                ]);
            }

            throw ValidationException::withMessages([
                'email' => ['This email is already registered to another account.'],
            ]);
        }

        return DB::transaction(function () use ($tenant, $email, $password, $name) {
            $user = User::query()->create([
                'organization_id' => $tenant->organization_id,
                'name' => $name ?? $tenant->fullName(),
                'email' => $email,
                'password' => Hash::make($password),
                'account_type' => UserAccountType::Tenant,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]);

            $tenant->update([
                'user_id' => $user->id,
                'email' => $email,
            ]);

            return $user;
        });
    }
}
