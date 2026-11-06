<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TenantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(TenantSeeder::class);
    }

    public function test_owner_can_open_reports_hub_and_export_occupancy_csv(): void
    {
        $owner = User::query()->where('email', 'owner@demo-dorm.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('organization.reports.hub'))
            ->assertOk()
            ->assertSee('Reports & analytics');

        $this->actingAs($owner)
            ->get(route('organization.reports.occupancy'))
            ->assertOk()
            ->assertSee('Occupancy report');

        $response = $this->actingAs($owner)
            ->get(route('organization.reports.export', ['report' => 'occupancy', 'format' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_tenant_cannot_access_organization_reports(): void
    {
        $tenantUser = User::query()->where('email', 'tenant@demo-dorm.test')->firstOrFail();

        $this->actingAs($tenantUser)
            ->get(route('organization.reports.hub'))
            ->assertForbidden();
    }

    public function test_financial_summary_export_requires_financial_gate(): void
    {
        $organization = Organization::factory()->create();
        $staff = User::factory()->create(['organization_id' => $organization->id]);

        setPermissionsTeamId($organization->id);
        $staff->assignRole('maintenance-staff');

        $this->actingAs($staff)
            ->get(route('organization.reports.export', [
                'report' => 'financial-summary',
                'format' => 'csv',
            ]))
            ->assertForbidden();
    }
}
