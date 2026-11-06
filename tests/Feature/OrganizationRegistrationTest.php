<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Enums\UserAccountType;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_organization_registration_creates_pending_organization_and_owner(): void
    {
        $response = $this->post(route('register.store'), [
            'organization_name' => 'Sunrise Dormitory',
            'organization_email' => 'hello@sunrise.test',
            'organization_phone' => '09171234567',
            'address_line1' => '123 Mabini St',
            'city' => 'Manila',
            'province' => 'Metro Manila',
            'owner_name' => 'Maria Santos',
            'owner_email' => 'maria@sunrise.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'registration-submitted');

        $organization = Organization::query()->where('email', 'hello@sunrise.test')->first();
        $this->assertNotNull($organization);
        $this->assertSame(OrganizationStatus::Pending, $organization->status);

        $owner = User::query()->where('email', 'maria@sunrise.test')->first();
        $this->assertNotNull($owner);
        $this->assertSame($organization->id, $owner->organization_id);
        $this->assertSame(UserAccountType::OrganizationOwner, $owner->account_type);
        $this->assertTrue($owner->hasRole('organization-owner'));
    }
}
