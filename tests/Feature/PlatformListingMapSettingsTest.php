<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Platform\PlatformListingMapSettings;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformListingMapSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);
    }

    private function superAdmin(): User
    {
        return User::query()->where('account_type', 'super_admin')->firstOrFail();
    }

    public function test_super_admin_can_update_listing_map_defaults(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('platform.settings.listing-map.update'), [
                'latitude' => '14.5995',
                'longitude' => '120.9842',
                'zoom' => '11',
                'label' => 'Metro Manila',
            ])
            ->assertRedirect();

        $defaults = PlatformListingMapSettings::defaults();

        $this->assertSame('Metro Manila', $defaults['label']);
        $this->assertEqualsWithDelta(14.5995, $defaults['latitude'], 0.0001);
        $this->assertSame(11, $defaults['zoom']);
    }

    public function test_public_home_uses_platform_listing_map_defaults(): void
    {
        PlatformListingMapSettings::update([
            'latitude' => 10.3157,
            'longitude' => 123.8854,
            'zoom' => 12,
            'label' => 'Cebu City',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('data-default-lat="10.3157"', false);
        $response->assertSee('data-default-lng="123.8854"', false);
        $response->assertSee('data-default-zoom="12"', false);
    }
}
