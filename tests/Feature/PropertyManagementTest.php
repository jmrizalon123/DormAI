<?php

namespace Tests\Feature;

use App\Enums\BedSpaceStatus;
use App\Enums\RoomStatus;
use App\Models\BedSpace;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Organization;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Room;
use App\Models\User;
use App\Services\Property\OccupancyService;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function organizationOwner(): User
    {
        $organization = Organization::factory()->create();
        $owner = User::factory()->organizationOwner()->create([
            'organization_id' => $organization->id,
        ]);

        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');

        return $owner;
    }

    public function test_properties_index_shows_portfolio_map_for_pinned_properties(): void
    {
        $owner = $this->organizationOwner();

        Property::factory()->create([
            'organization_id' => $owner->organization_id,
            'name' => 'Pinned Harbor Dorm',
            'email' => 'harbor@example.test',
            'phone' => '09171112222',
            'latitude' => 11.7753,
            'longitude' => 124.8861,
        ]);

        Property::factory()->create([
            'organization_id' => $owner->organization_id,
            'name' => 'No Pin Dorm',
            'email' => 'nopin@example.test',
            'phone' => '09173334444',
        ]);

        $response = $this->actingAs($owner)->get(route('organization.properties.index'));

        $response->assertOk();
        $response->assertSee('organizationPropertiesMap', false);
        $response->assertSee('Pinned Harbor Dorm', false);
        $response->assertSee('Portfolio map', false);
    }

    public function test_owner_can_update_property(): void
    {
        $owner = $this->organizationOwner();

        $property = Property::factory()->create([
            'organization_id' => $owner->organization_id,
            'name' => 'Old Name Dorm',
            'email' => 'old@example.test',
            'phone' => '09170001111',
        ]);

        $this->actingAs($owner)->put(route('organization.properties.update', $property), [
            'name' => 'Renamed Dorm',
            'status' => 'active',
            'email' => 'new@example.test',
            'phone' => '09179998888',
            'city' => 'Catbalogan',
            'listing' => [
                'tagline' => 'Updated tagline',
            ],
        ])->assertRedirect(route('organization.properties.show', $property));

        $property->refresh();

        $this->assertSame('Renamed Dorm', $property->name);
        $this->assertSame('new@example.test', $property->email);
        $this->assertSame('Updated tagline', $property->settings['listing']['tagline']);
    }

    public function test_owner_can_create_property(): void
    {
        $owner = $this->organizationOwner();

        $response = $this->actingAs($owner)->post(route('organization.properties.store'), [
            'name' => 'Campus View Dorm',
            'code' => 'CVD1',
            'status' => 'active',
            'email' => 'frontdesk@campusview.test',
            'phone' => '09171234567',
            'city' => 'Quezon City',
            'province' => 'Metro Manila',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('properties', [
            'name' => 'Campus View Dorm',
            'organization_id' => $owner->organization_id,
        ]);
    }

    public function test_owner_can_create_property_with_multiple_images(): void
    {
        Storage::fake('public');

        $owner = $this->organizationOwner();

        $response = $this->actingAs($owner)->post(route('organization.properties.store'), [
            'name' => 'Gallery Dorm',
            'status' => 'active',
            'email' => 'gallery@example.test',
            'phone' => '09180001111',
            'images' => [
                UploadedFile::fake()->image('front.jpg', 800, 600),
                UploadedFile::fake()->image('lobby.png', 640, 480),
            ],
        ]);

        $property = Property::query()->where('name', 'Gallery Dorm')->firstOrFail();

        $response->assertRedirect(route('organization.properties.show', $property));

        $this->assertSame(2, PropertyImage::query()->where('property_id', $property->id)->count());

        $primary = PropertyImage::query()->where('property_id', $property->id)->where('is_primary', true)->first();
        $this->assertNotNull($primary);
        $this->assertSame('front.jpg', $primary->original_name);
        Storage::disk('public')->assertExists($primary->path);
    }

    public function test_owner_can_create_property_with_listing_amenities(): void
    {
        $owner = $this->organizationOwner();

        $this->actingAs($owner)->post(route('organization.properties.store'), [
            'name' => 'Amenity Dorm',
            'status' => 'active',
            'email' => 'amenity@example.test',
            'phone' => '09182223333',
            'listing' => [
                'tagline' => 'Quiet dorm near campus',
                'amenities' => ['wifi', 'study_lounge'],
                'highlights' => "Fiber internet\nStudy tables",
                'starting_rate' => '4200',
                'public_listing' => '1',
                'wifi_included' => '1',
            ],
        ])->assertRedirect();

        $property = Property::query()->where('name', 'Amenity Dorm')->firstOrFail();

        $this->assertSame('Quiet dorm near campus', $property->settings['listing']['tagline']);
        $this->assertContains('wifi', $property->settings['listing']['amenities']);
        $this->assertEquals(4200, $property->settings['listing']['starting_rate']);
    }

    public function test_owner_can_save_property_map_coordinates(): void
    {
        $owner = $this->organizationOwner();

        $this->actingAs($owner)->post(route('organization.properties.store'), [
            'name' => 'Mapped Dorm',
            'status' => 'active',
            'email' => 'mapped@example.test',
            'phone' => '09183334444',
            'latitude' => '14.6760413',
            'longitude' => '121.0437003',
        ])->assertRedirect();

        $property = Property::query()->where('name', 'Mapped Dorm')->firstOrFail();

        $this->assertTrue($property->hasCoordinates());
        $this->assertEqualsWithDelta(14.6760413, (float) $property->latitude, 0.0001);
    }

    public function test_owner_cannot_view_property_from_another_organization(): void
    {
        $owner = $this->organizationOwner();
        $otherProperty = Property::factory()->create();

        $response = $this->actingAs($owner)->get(route('organization.properties.show', $otherProperty));

        $response->assertNotFound();
    }

    public function test_room_status_updates_when_bed_is_marked_occupied(): void
    {
        $organization = Organization::factory()->create();
        $property = Property::factory()->create(['organization_id' => $organization->id]);
        $building = Building::factory()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
        ]);
        $floor = Floor::factory()->create([
            'organization_id' => $organization->id,
            'building_id' => $building->id,
        ]);
        $room = Room::factory()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'capacity' => 2,
            'status' => RoomStatus::Available,
        ]);

        BedSpace::factory()->create(['organization_id' => $organization->id, 'room_id' => $room->id, 'label' => 'A']);
        $bedB = BedSpace::factory()->create(['organization_id' => $organization->id, 'room_id' => $room->id, 'label' => 'B']);

        $bedB->update(['status' => BedSpaceStatus::Occupied]);
        app(OccupancyService::class)->syncRoomStatus($room->fresh(['bedSpaces']));

        $this->assertSame(RoomStatus::Partial, $room->fresh()->status);
    }
}
