<?php

namespace Tests\Feature;

use App\Enums\MaintenanceRequestStatus;
use App\Enums\WorkOrderStatus;
use App\Models\MaintenanceRequest;
use App\Models\Organization;
use App\Models\Property;
use App\Models\User;
use App\Services\Maintenance\MaintenanceService;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * @return array{organization: Organization, owner: User, staff: User, property: Property}
     */
    private function maintenanceTeam(): array
    {
        $organization = Organization::factory()->create();
        $owner = User::factory()->organizationOwner()->create(['organization_id' => $organization->id]);
        $staff = User::factory()->create(['organization_id' => $organization->id]);
        $property = Property::factory()->create(['organization_id' => $organization->id]);

        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');
        $staff->assignRole('maintenance-staff');

        return compact('organization', 'owner', 'staff', 'property');
    }

    public function test_request_to_work_order_assignment_and_completion(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'staff' => $staff, 'property' => $property] = $this->maintenanceTeam();

        $service = app(MaintenanceService::class);

        $request = $service->createRequest($organization, $owner, [
            'property_id' => $property->id,
            'title' => 'Leaking faucet',
            'description' => 'Bathroom sink leak',
            'priority' => 'high',
        ]);

        $workOrder = $service->createWorkOrderFromRequest($request, $owner);
        $this->assertSame(MaintenanceRequestStatus::WorkOrderCreated, $request->fresh()->status);

        $service->assign($workOrder, $owner, $staff);
        $workOrder->refresh();
        $this->assertSame(WorkOrderStatus::Assigned, $workOrder->status);
        $this->assertSame($staff->id, $workOrder->assigned_to);

        $service->complete($workOrder, $staff, 500, 150, 'Replaced washer');
        $workOrder->refresh();
        $this->assertSame(WorkOrderStatus::Completed, $workOrder->status);
        $this->assertSame('650.00', $workOrder->total_cost);
        $this->assertSame(MaintenanceRequestStatus::Completed, $request->fresh()->status);
    }

    public function test_work_order_isolated_by_organization(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'property' => $property] = $this->maintenanceTeam();

        $workOrder = app(MaintenanceService::class)->createStandaloneWorkOrder($organization, $owner, [
            'property_id' => $property->id,
            'title' => 'Paint hallway',
            'priority' => 'normal',
        ]);

        $otherOrg = Organization::factory()->create();
        $otherOwner = User::factory()->organizationOwner()->create(['organization_id' => $otherOrg->id]);
        setPermissionsTeamId($otherOrg->id);
        $otherOwner->assignRole('organization-owner');

        setPermissionsTeamId($otherOrg->id);
        $this->actingAs($otherOwner)
            ->get(route('organization.work-orders.show', $workOrder))
            ->assertNotFound();
    }

    public function test_maintenance_history_page_loads_for_owner(): void
    {
        ['owner' => $owner] = $this->maintenanceTeam();

        setPermissionsTeamId($owner->organization_id);
        $this->actingAs($owner)
            ->get(route('organization.maintenance-history.index'))
            ->assertOk();
    }

    public function test_owner_can_log_request_cancel_and_create_work_order_via_http(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'property' => $property] = $this->maintenanceTeam();

        setPermissionsTeamId($organization->id);

        $this->actingAs($owner)
            ->post(route('organization.maintenance-requests.store'), [
                'property_id' => $property->id,
                'title' => 'Broken lock',
                'description' => 'Room door lock stuck',
                'priority' => 'urgent',
            ])
            ->assertRedirect();

        $maintenanceRequest = MaintenanceRequest::query()->where('organization_id', $organization->id)->first();
        $this->assertNotNull($maintenanceRequest);
        $this->assertSame(MaintenanceRequestStatus::Submitted, $maintenanceRequest->status);

        $this->actingAs($owner)
            ->post(route('organization.maintenance-requests.cancel', $maintenanceRequest), [
                'reason' => 'Tenant fixed it themselves',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(MaintenanceRequestStatus::Cancelled, $maintenanceRequest->fresh()->status);

        $openRequest = app(MaintenanceService::class)->createRequest($organization, $owner, [
            'property_id' => $property->id,
            'title' => 'AC not cooling',
            'description' => 'Needs service',
            'priority' => 'high',
        ]);

        $this->actingAs($owner)
            ->post(route('organization.maintenance-requests.work-order', $openRequest))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($openRequest->fresh()->workOrder);
    }

    public function test_receptionist_can_view_but_not_log_requests(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'property' => $property] = $this->maintenanceTeam();
        $receptionist = User::factory()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $receptionist->assignRole('receptionist');

        $request = app(MaintenanceService::class)->createRequest($organization, $owner, [
            'property_id' => $property->id,
            'title' => 'Light bulb out',
            'description' => 'Hallway',
            'priority' => 'low',
        ]);

        setPermissionsTeamId($organization->id);
        $this->actingAs($receptionist)->get(route('organization.maintenance-requests.index'))->assertOk();
        $this->actingAs($receptionist)->get(route('organization.maintenance-requests.show', $request))->assertOk();
        $this->actingAs($receptionist)->get(route('organization.maintenance-requests.create'))->assertForbidden();
    }
}
