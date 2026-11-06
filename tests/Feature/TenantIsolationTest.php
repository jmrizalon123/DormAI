<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_organization_owner_cannot_edit_user_from_another_organization(): void
    {
        $organizationA = Organization::factory()->create();
        $organizationB = Organization::factory()->create();

        $ownerA = User::factory()->organizationOwner()->create([
            'organization_id' => $organizationA->id,
        ]);
        setPermissionsTeamId($organizationA->id);
        $ownerA->assignRole('organization-owner');

        $staffB = User::factory()->create([
            'organization_id' => $organizationB->id,
            'email' => 'staff-b@example.test',
        ]);
        setPermissionsTeamId($organizationB->id);
        $staffB->assignRole('receptionist');

        $response = $this->actingAs($ownerA)->get(route('organization.users.edit', $staffB));

        $response->assertForbidden();
    }
}
