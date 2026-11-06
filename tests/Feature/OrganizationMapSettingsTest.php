<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Support\Organization\OrganizationMapSettings;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationMapSettingsTest extends TestCase
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

    public function test_system_default_is_catbalogan(): void
    {
        $defaults = OrganizationMapSettings::systemDefaults();

        $this->assertStringContainsString('Catbalogan', $defaults['label']);
        $this->assertEqualsWithDelta(11.7753, $defaults['latitude'], 0.01);
    }

    public function test_owner_can_update_organization_map_defaults(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->put(route('organization.settings.map.update'), [
                'latitude' => '11.7800',
                'longitude' => '124.8900',
                'zoom' => '14',
                'label' => 'Downtown Catbalogan',
            ])
            ->assertRedirect();

        $map = OrganizationMapSettings::forOrganization($owner->organization);

        $this->assertSame('Downtown Catbalogan', $map['label']);
        $this->assertEqualsWithDelta(11.78, $map['latitude'], 0.001);
        $this->assertSame(14, $map['zoom']);
    }
}
