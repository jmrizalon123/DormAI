<?php

namespace App\Services\Organization;

use App\Enums\OrganizationStatus;
use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationRegistrationService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $slug = $this->generateUniqueSlug($data['organization_name']);

            $organization = Organization::query()->create([
                'name' => $data['organization_name'],
                'slug' => $slug,
                'email' => $data['organization_email'],
                'phone' => $data['organization_phone'] ?? null,
                'address_line1' => $data['address_line1'] ?? null,
                'city' => $data['city'] ?? null,
                'province' => $data['province'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? 'PH',
                'status' => OrganizationStatus::Pending,
            ]);

            $user = User::query()->create([
                'organization_id' => $organization->id,
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'password' => $data['password'],
                'phone' => $data['owner_phone'] ?? null,
                'account_type' => UserAccountType::OrganizationOwner,
                'status' => UserStatus::Active,
            ]);

            setPermissionsTeamId($organization->id);
            $user->assignRole('organization-owner');

            $this->auditLogger->log(
                event: 'organization.registered',
                auditable: $organization,
                properties: [
                    'organization_id' => $organization->id,
                    'owner_user_id' => $user->id,
                ],
                organizationId: $organization->id,
            );

            return $user;
        });
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Organization::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
