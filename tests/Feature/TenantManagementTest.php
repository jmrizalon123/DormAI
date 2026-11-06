<?php

namespace Tests\Feature;

use App\Enums\BedSpaceStatus;
use App\Enums\TenantAssignmentStatus;
use App\Models\BedSpace;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Tenant\TenantAssignmentService;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function owner(): User
    {
        $organization = Organization::factory()->create();
        $owner = User::factory()->organizationOwner()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');

        return $owner;
    }

    public function test_owner_can_register_tenant(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->post(route('organization.tenants.store'), [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.test',
            'phone' => '09171234567',
            'status' => 'prospect',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'organization_id' => $owner->organization_id,
        ]);
    }

    public function test_owner_cannot_view_tenant_from_another_organization(): void
    {
        $owner = $this->owner();
        $otherTenant = Tenant::factory()->create();

        $this->actingAs($owner)
            ->get(route('organization.tenants.show', $otherTenant))
            ->assertNotFound();
    }

    public function test_assigning_tenant_to_bed_marks_bed_occupied(): void
    {
        $organization = Organization::factory()->create();
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

        app(TenantAssignmentService::class)->assign($tenant, $bed, now()->toDateString());

        $this->assertSame(BedSpaceStatus::Occupied, $bed->fresh()->status);
        $this->assertDatabaseHas('tenant_assignments', [
            'tenant_id' => $tenant->id,
            'bed_space_id' => $bed->id,
            'status' => TenantAssignmentStatus::Active->value,
        ]);
    }

    public function test_move_out_frees_bed_and_ends_assignment(): void
    {
        $organization = Organization::factory()->create();
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

        $service = app(TenantAssignmentService::class);
        $assignment = $service->assign($tenant, $bed, now()->toDateString());
        $service->moveOut($assignment, now()->toDateString(), 'Graduated');

        $this->assertSame(BedSpaceStatus::Available, $bed->fresh()->status);
        $this->assertSame(TenantAssignmentStatus::Ended, $assignment->fresh()->status);
    }

    public function test_tenant_list_can_be_filtered_by_search(): void
    {
        $owner = $this->owner();
        Tenant::factory()->create([
            'organization_id' => $owner->organization_id,
            'first_name' => 'Unique',
            'last_name' => 'AlphaTest',
        ]);
        Tenant::factory()->create([
            'organization_id' => $owner->organization_id,
            'first_name' => 'Other',
            'last_name' => 'Person',
        ]);

        $response = $this->actingAs($owner)->get(route('organization.tenants.index', ['search' => 'AlphaTest']));

        $response->assertOk();
        $response->assertSee('Unique AlphaTest');
        $response->assertDontSee('Other Person');
    }

    public function test_tenant_document_download_requires_authorization(): void
    {
        Storage::fake('local');
        $owner = $this->owner();
        $otherTenant = Tenant::factory()->create(['organization_id' => $owner->organization_id]);
        $intruderOrg = Organization::factory()->create();
        $intruder = User::factory()->organizationOwner()->create(['organization_id' => $intruderOrg->id]);
        setPermissionsTeamId($intruderOrg->id);
        $intruder->assignRole('organization-owner');

        $path = 'tenant-documents/'.$owner->organization_id.'/'.$otherTenant->id.'/id.pdf';
        Storage::disk('local')->put($path, 'secret');
        $document = $otherTenant->documents()->create([
            'organization_id' => $owner->organization_id,
            'category' => 'valid_id',
            'path' => $path,
            'original_name' => 'id.pdf',
        ]);

        $this->actingAs($intruder)
            ->get(route('organization.tenants.documents.show', [$otherTenant, $document]))
            ->assertNotFound();
    }
}
