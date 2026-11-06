<?php

namespace App\Support\Tenancy;

use App\Enums\UserAccountType;
use App\Models\Tenant;
use App\Models\User;
use RuntimeException;

class TenantPortalContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function resolveForUser(User $user): void
    {
        if (! $user->isTenant()) {
            $this->tenant = null;

            return;
        }

        $this->tenant = Tenant::query()
            ->where('user_id', $user->id)
            ->when($user->organization_id !== null, fn ($query) => $query->where('organization_id', $user->organization_id))
            ->first();

        if ($this->tenant === null && $user->organization_id !== null) {
            $this->tenant = Tenant::query()
                ->where('organization_id', $user->organization_id)
                ->whereNull('user_id')
                ->whereRaw('LOWER(email) = ?', [strtolower($user->email)])
                ->first();

            if ($this->tenant !== null) {
                $this->tenant->update(['user_id' => $user->id]);
            }
        }

        if ($this->tenant === null && $user->organization_id !== null) {
            setPermissionsTeamId($user->organization_id);

            if ($user->hasRole('organization-owner')) {
                $user->forceFill(['account_type' => UserAccountType::OrganizationOwner])->save();
            }
        }
    }

    public function ensureTenant(): Tenant
    {
        $tenant = $this->tenant();

        if ($tenant === null) {
            throw new RuntimeException('No tenant portal profile is available.');
        }

        return $tenant;
    }
}
