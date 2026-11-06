<?php

namespace App\Support\Tenancy;

use App\Models\Organization;
use App\Models\User;
use RuntimeException;

class OrganizationContext
{
    private ?Organization $organization = null;

    private bool $locked = false;

    public function set(Organization $organization): void
    {
        if ($this->locked) {
            throw new RuntimeException('Organization context is locked for this request.');
        }

        $this->organization = $organization;
    }

    public function clear(): void
    {
        if ($this->locked) {
            throw new RuntimeException('Organization context is locked for this request.');
        }

        $this->organization = null;
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function id(): ?int
    {
        return $this->organization?->id;
    }

    public function organization(): ?Organization
    {
        return $this->organization;
    }

    public function hasOrganization(): bool
    {
        return $this->organization !== null;
    }

    public function resolveForUser(User $user): void
    {
        if ($this->locked) {
            return;
        }

        if ($user->isSuperAdmin()) {
            $this->organization = null;

            return;
        }

        if ($user->organization_id === null) {
            $this->organization = null;

            return;
        }

        $user->loadMissing('organization');
        $this->organization = $user->organization;
    }

    public function ensureId(): int
    {
        $id = $this->id();

        if ($id === null) {
            throw new RuntimeException('No organization context is available.');
        }

        return $id;
    }

    public function ensureOrganization(): Organization
    {
        $organization = $this->organization();

        if ($organization === null) {
            throw new RuntimeException('No organization context is available.');
        }

        return $organization;
    }
}
