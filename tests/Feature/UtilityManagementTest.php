<?php

namespace Tests\Feature;

use App\Enums\TenantAssignmentStatus;
use App\Enums\UtilityAllocationStatus;
use App\Enums\UtilityType;
use App\Models\BedSpace;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\TenantAssignment;
use App\Models\User;
use App\Models\UtilityMeter;
use App\Models\UtilityRate;
use App\Services\Utilities\UtilityAllocationService;
use App\Services\Utilities\UtilityReadingService;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * @return array{organization: Organization, owner: User, property: Property, room: Room, tenant: Tenant}
     */
    private function utilityContext(): array
    {
        $organization = Organization::factory()->create();
        $owner = User::factory()->organizationOwner()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');

        $property = Property::factory()->create(['organization_id' => $organization->id]);
        $building = Building::factory()->create(['organization_id' => $organization->id, 'property_id' => $property->id]);
        $floor = Floor::factory()->create(['organization_id' => $organization->id, 'building_id' => $building->id]);
        $room = Room::factory()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);
        $bed = BedSpace::factory()->create(['organization_id' => $organization->id, 'room_id' => $room->id]);
        $tenant = Tenant::factory()->create(['organization_id' => $organization->id]);

        TenantAssignment::query()->create([
            'organization_id' => $organization->id,
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'room_id' => $room->id,
            'bed_space_id' => $bed->id,
            'move_in_date' => now()->subMonth(),
            'status' => TenantAssignmentStatus::Active,
        ]);

        UtilityRate::query()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'utility_type' => UtilityType::Electricity,
            'rate_per_unit' => 12.5,
            'unit_label' => 'kWh',
            'effective_from' => now()->subYear(),
            'is_active' => true,
        ]);

        return compact('organization', 'owner', 'property', 'room', 'tenant');
    }

    public function test_reading_calculates_consumption_from_previous(): void
    {
        ['owner' => $owner, 'property' => $property] = $this->utilityContext();

        $meter = UtilityMeter::query()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'utility_type' => UtilityType::Water,
            'label' => 'Main water',
            'is_shared' => true,
            'is_active' => true,
        ]);

        $first = app(UtilityReadingService::class)->record($meter, $owner, now()->subMonth()->toDateString(), 100);
        $second = app(UtilityReadingService::class)->record($meter, $owner, now()->toDateString(), 145);

        $this->assertSame('45.000', $second->consumption);
        $this->assertSame('100.000', $first->current_reading);
    }

    public function test_shared_meter_splits_consumption_across_occupied_rooms(): void
    {
        ['owner' => $owner, 'property' => $property, 'room' => $room] = $this->utilityContext();

        $meter = UtilityMeter::query()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'utility_type' => UtilityType::Electricity,
            'label' => 'Shared power',
            'is_shared' => true,
            'is_active' => true,
        ]);

        $reading = app(UtilityReadingService::class)->record($meter, $owner, now()->toDateString(), 200);
        $allocations = app(UtilityAllocationService::class)->calculate($reading);

        $this->assertCount(1, $allocations);
        $this->assertSame($room->id, $allocations->first()->room_id);
        $this->assertSame('200.000', $allocations->first()->consumption_units);
        $this->assertSame('2500.00', $allocations->first()->amount);
        $this->assertSame(UtilityAllocationStatus::Calculated, $allocations->first()->status);
    }

    public function test_individual_room_meter_allocates_full_consumption(): void
    {
        ['owner' => $owner, 'property' => $property, 'room' => $room] = $this->utilityContext();

        $meter = UtilityMeter::query()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'utility_type' => UtilityType::Electricity,
            'label' => 'Room submeter',
            'is_shared' => false,
            'is_active' => true,
        ]);

        $reading = app(UtilityReadingService::class)->record($meter, $owner, now()->toDateString(), 80);
        $allocations = app(UtilityAllocationService::class)->calculate($reading);

        $this->assertCount(1, $allocations);
        $this->assertSame('1000.00', $allocations->first()->amount);
    }

    public function test_cross_tenant_reading_is_not_accessible(): void
    {
        ['property' => $property, 'owner' => $owner] = $this->utilityContext();

        $otherOrg = Organization::factory()->create();
        $otherOwner = User::factory()->organizationOwner()->create(['organization_id' => $otherOrg->id]);
        setPermissionsTeamId($otherOrg->id);
        $otherOwner->assignRole('organization-owner');

        $meter = UtilityMeter::query()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'utility_type' => UtilityType::Water,
            'label' => 'Private',
            'is_shared' => true,
            'is_active' => true,
        ]);

        $reading = app(UtilityReadingService::class)->record($meter, $owner, now()->toDateString(), 10);

        setPermissionsTeamId($otherOrg->id);
        $this->actingAs($otherOwner)
            ->get(route('organization.utility-readings.show', $reading))
            ->assertNotFound();
    }
}
