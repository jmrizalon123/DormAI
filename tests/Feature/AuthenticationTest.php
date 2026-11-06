<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_pending_organization_owner_cannot_sign_in(): void
    {
        $organization = Organization::factory()->pending()->create();
        $owner = User::factory()->organizationOwner()->create([
            'organization_id' => $organization->id,
            'email' => 'owner@example.test',
        ]);

        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');

        $response = $this->post(route('login'), [
            'email' => $owner->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_active_organization_owner_can_sign_in(): void
    {
        $organization = Organization::factory()->create([
            'status' => OrganizationStatus::Active,
        ]);
        $owner = User::factory()->organizationOwner()->create([
            'organization_id' => $organization->id,
            'email' => 'owner@example.test',
        ]);

        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');

        $response = $this->post(route('login'), [
            'email' => $owner->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($owner);
        $response->assertRedirect(route('organization.dashboard'));
    }
}
