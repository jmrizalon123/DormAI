<?php

namespace Database\Seeders;

use App\Enums\BedSpaceStatus;
use App\Enums\FloorStatus;
use App\Enums\OrganizationStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use App\Models\BedSpace;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\TenantEmergencyContact;
use App\Models\User;
use App\Services\Portal\TenantPortalAccessService;
use App\Services\Tenant\TenantAssignmentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $organization = $this->resolveOrganization();
        $beds = $this->ensureAvailableBeds($organization);

        $tenants = $this->seedTenants($organization);
        $this->assignActiveTenants($organization, $tenants, $beds);
        $this->seedPortalDemoUser($tenants);

        $this->command?->info(sprintf(
            'Seeded %d tenant(s) for organization "%s" (%s).',
            count($tenants),
            $organization->name,
            $organization->slug,
        ));
    }

    private function resolveOrganization(): Organization
    {
        $slug = (string) env('TENANT_SEED_ORGANIZATION_SLUG', 'demo-dormitory');
        $platformSlug = (string) config('dormai.platform_organization_slug');

        $organization = Organization::query()
            ->where('slug', $slug)
            ->where('slug', '!=', $platformSlug)
            ->first();

        if ($organization === null) {
            $organization = Organization::query()->create([
                'name' => 'Demo Dormitory',
                'slug' => $slug,
                'email' => env('TENANT_SEED_ORGANIZATION_EMAIL', 'demo@dormai.test'),
                'phone' => '09171234567',
                'address_line1' => '123 Sample Street',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'country' => 'PH',
                'status' => OrganizationStatus::Active,
                'approved_at' => now(),
            ]);
        } elseif ($organization->status !== OrganizationStatus::Active) {
            $organization->update([
                'status' => OrganizationStatus::Active,
                'approved_at' => $organization->approved_at ?? now(),
            ]);
        }

        $this->ensureOrganizationOwner($organization);

        return $organization->fresh();
    }

    private function ensureOrganizationOwner(Organization $organization): void
    {
        $email = (string) env('TENANT_SEED_OWNER_EMAIL', 'owner@demo-dorm.test');
        $password = (string) env('TENANT_SEED_OWNER_PASSWORD', 'password');

        $owner = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'organization_id' => $organization->id,
                'name' => 'Demo Dorm Owner',
                'password' => $password,
                'account_type' => UserAccountType::OrganizationOwner,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        setPermissionsTeamId($organization->id);
        if (! $owner->hasRole('organization-owner')) {
            $owner->assignRole('organization-owner');
        }

        if ($owner->account_type !== UserAccountType::OrganizationOwner) {
            $owner->forceFill(['account_type' => UserAccountType::OrganizationOwner])->save();
        }
    }

    /**
     * @return Collection<int, BedSpace>
     */
    private function ensureAvailableBeds(Organization $organization): Collection
    {
        $existing = BedSpace::query()
            ->where('organization_id', $organization->id)
            ->where('status', BedSpaceStatus::Available)
            ->orderBy('id')
            ->limit(6)
            ->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $property = Property::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'DEMO01',
            ],
            [
                'name' => 'Main Boarding House',
                'email' => 'main@'.$organization->slug.'.test',
                'phone' => '09181234567',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'country' => 'PH',
                'status' => PropertyStatus::Active,
            ],
        );

        $building = Building::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'property_id' => $property->id,
                'name' => 'Building A',
            ],
            [
                'description' => 'Demo building for seeded tenants',
            ],
        );

        $floor = Floor::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'building_id' => $building->id,
                'level_number' => 1,
            ],
            [
                'name' => 'Ground Floor',
                'status' => FloorStatus::Active,
            ],
        );

        $room = Room::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'floor_id' => $floor->id,
                'room_number' => '101',
            ],
            [
                'property_id' => $property->id,
                'building_id' => $building->id,
                'capacity' => 4,
                'monthly_rate' => 3500,
                'status' => RoomStatus::Available,
            ],
        );

        $labels = ['A', 'B', 'C', 'D'];

        foreach ($labels as $label) {
            BedSpace::query()->firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'room_id' => $room->id,
                    'label' => 'Bed '.$label,
                ],
                [
                    'monthly_rate' => 3500,
                    'status' => BedSpaceStatus::Available,
                ],
            );
        }

        return BedSpace::query()
            ->where('organization_id', $organization->id)
            ->where('status', BedSpaceStatus::Available)
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<int, Tenant>
     */
    private function seedTenants(Organization $organization): array
    {
        $samples = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan.delacruz@demo-dorm.test',
                'phone' => '09171111111',
                'school_or_work' => 'UP Diliman',
                'status' => TenantStatus::Active,
                'emergency' => ['name' => 'Rosa Dela Cruz', 'relationship' => 'Mother', 'phone' => '09172222222'],
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'maria.santos@demo-dorm.test',
                'phone' => '09173333333',
                'school_or_work' => 'Ateneo de Manila',
                'status' => TenantStatus::Active,
                'emergency' => ['name' => 'Pedro Santos', 'relationship' => 'Father', 'phone' => '09174444444'],
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Reyes',
                'email' => 'ana.reyes@demo-dorm.test',
                'phone' => '09175555555',
                'school_or_work' => 'Remote worker',
                'status' => TenantStatus::Active,
            ],
            [
                'first_name' => 'Jose',
                'last_name' => 'Garcia',
                'email' => 'jose.garcia@demo-dorm.test',
                'phone' => '09176666666',
                'status' => TenantStatus::Prospect,
            ],
            [
                'first_name' => 'Liza',
                'last_name' => 'Mendoza',
                'email' => 'liza.mendoza@demo-dorm.test',
                'phone' => '09177777777',
                'school_or_work' => 'De La Salle University',
                'status' => TenantStatus::Prospect,
            ],
            [
                'first_name' => 'Mark',
                'last_name' => 'Torres',
                'email' => 'mark.torres@demo-dorm.test',
                'phone' => '09178888888',
                'status' => TenantStatus::MovedOut,
            ],
        ];

        $tenants = [];

        foreach ($samples as $sample) {
            $emergency = $sample['emergency'] ?? null;
            unset($sample['emergency']);

            /** @var Tenant $tenant */
            $tenant = Tenant::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'email' => $sample['email'],
                ],
                array_merge($sample, [
                    'organization_id' => $organization->id,
                    'city' => 'Quezon City',
                    'province' => 'Metro Manila',
                ]),
            );

            if ($emergency !== null) {
                TenantEmergencyContact::query()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'phone' => $emergency['phone'],
                    ],
                    array_merge($emergency, [
                        'organization_id' => $organization->id,
                        'tenant_id' => $tenant->id,
                        'is_primary' => true,
                    ]),
                );
            }

            $tenants[] = $tenant->fresh();
        }

        return $tenants;
    }

    /**
     * @param  array<int, Tenant>  $tenants
     * @param  Collection<int, BedSpace>  $beds
     */
    /**
     * @param  array<int, Tenant>  $tenants
     */
    private function seedPortalDemoUser(array $tenants): void
    {
        $email = (string) env('TENANT_PORTAL_DEMO_EMAIL', 'tenant@demo-dorm.test');
        $password = (string) env('TENANT_PORTAL_DEMO_PASSWORD', 'password');

        foreach ($tenants as $tenant) {
            if ($tenant->status !== TenantStatus::Active || $tenant->user_id !== null) {
                continue;
            }

            if (! $tenant->activeAssignment()->exists()) {
                continue;
            }

            app(TenantPortalAccessService::class)->enable($tenant, $email, $password);

            $this->command?->info(sprintf('Tenant portal demo login: %s / %s', $email, $password));

            return;
        }
    }

    /**
     * @param  array<int, Tenant>  $tenants
     * @param  Collection<int, BedSpace>  $beds
     */
    private function assignActiveTenants(Organization $organization, array $tenants, Collection $beds): void
    {
        $assignmentService = app(TenantAssignmentService::class);
        $moveInDate = now()->subMonths(2)->toDateString();
        $bedIndex = 0;

        foreach ($tenants as $tenant) {
            if ($tenant->status !== TenantStatus::Active) {
                continue;
            }

            if ($tenant->activeAssignment()->exists()) {
                continue;
            }

            while ($bedIndex < $beds->count()) {
                $bed = $beds[$bedIndex];
                $bedIndex++;

                if ($bed->fresh()->status !== BedSpaceStatus::Available) {
                    continue;
                }

                $assignmentService->assign($tenant, $bed, $moveInDate);

                break;
            }
        }
    }
}
