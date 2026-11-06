<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);
    }

    public function test_super_admin_can_approve_pending_organization(): void
    {
        $organization = Organization::factory()->pending()->create();
        $admin = User::query()->where('email', 'admin@dormai.test')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('platform.organizations.approve', $organization));

        $response->assertRedirect();
        $organization->refresh();
        $this->assertSame(OrganizationStatus::Active, $organization->status);
        $this->assertNotNull($organization->approved_at);
    }
}
