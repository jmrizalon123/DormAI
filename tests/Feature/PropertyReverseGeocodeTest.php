<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Services\Property\PropertyGeocodeService;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PropertyReverseGeocodeTest extends TestCase
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

    public function test_reverse_geocode_endpoint_returns_address_fields(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/reverse*' => Http::response([
                'display_name' => 'Rizal Avenue, Catbalogan, Samar, Philippines',
                'address' => [
                    'road' => 'Rizal Avenue',
                    'city' => 'Catbalogan',
                    'state' => 'Samar',
                    'postcode' => '6700',
                ],
            ]),
        ]);

        $owner = $this->owner();

        $this->actingAs($owner)
            ->getJson(route('organization.properties.reverse-geocode', [
                'latitude' => 11.7753,
                'longitude' => 124.8861,
            ]))
            ->assertOk()
            ->assertJsonPath('city', 'Catbalogan')
            ->assertJsonPath('province', 'Samar')
            ->assertJsonPath('postal_code', '6700');
    }

    public function test_reverse_geocode_service_maps_nominatim_address(): void
    {
        Http::fake([
            'nominatim.openstreetmap.org/reverse*' => Http::response([
                'display_name' => 'Sample',
                'address' => [
                    'house_number' => '12',
                    'road' => 'Main Street',
                    'suburb' => 'Downtown',
                    'municipality' => 'Catbalogan',
                    'state' => 'Samar',
                    'postcode' => '6700',
                ],
            ]),
        ]);

        $result = app(PropertyGeocodeService::class)->reverse(11.77, 124.88);

        $this->assertNotNull($result);
        $this->assertSame('12 Main Street', $result['address_line1']);
        $this->assertSame('Downtown', $result['address_line2']);
        $this->assertSame('Catbalogan', $result['city']);
    }
}
