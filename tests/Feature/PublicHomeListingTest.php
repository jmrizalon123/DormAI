<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Enums\PropertyStatus;
use App\Models\Organization;
use App\Models\Property;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHomeListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_home_page_lists_public_active_properties(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Active]);

        $listed = Property::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Harbor View Dorm',
            'city' => 'Catbalogan',
            'province' => 'Samar',
            'status' => PropertyStatus::Active,
            'email' => 'harbor@example.test',
            'phone' => '09171112222',
            'settings' => [
                'listing' => [
                    'public_listing' => true,
                    'tagline' => 'Near campus',
                    'nearby_landmarks' => '5 min walk to university',
                    'house_rules' => 'Quiet hours 10 PM',
                    'gender_policy' => 'mixed',
                    'min_stay_months' => 3,
                ],
            ],
            'description' => 'Spacious dorm for students.',
            'address_line1' => '123 Main St',
            'city' => 'Catbalogan',
            'province' => 'Samar',
        ]);

        Property::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Hidden Dorm',
            'status' => PropertyStatus::Active,
            'email' => 'hidden@example.test',
            'phone' => '09173334444',
            'settings' => ['listing' => ['public_listing' => false]],
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Harbor View Dorm', false);
        $response->assertDontSee('Hidden Dorm', false);
        $response->assertSee('publicHomeMap', false);

        $detail = $this->get(route('public.listings.show', $listed));

        $detail->assertOk();
        $detail->assertSee('Property details', false);
        $detail->assertSee('Spacious dorm for students.', false);
        $detail->assertSee('5 min walk to university', false);
        $detail->assertSee('Quiet hours 10 PM', false);
        $detail->assertSee('123 Main St', false);
    }
}
