<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Support\Platform\PlatformListingContentSettings;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlatformListingContentSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);
        Storage::fake('public');
    }

    private function superAdmin(): User
    {
        return User::query()->where('account_type', 'super_admin')->firstOrFail();
    }

    public function test_super_admin_can_update_listing_content_text(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('platform.settings.listing-content.update'), [
                'brand_title_primary' => 'Dorm',
                'brand_title_accent' => 'Hub',
                'brand_tagline' => 'Find a room near campus',
                'hero_title' => 'Find your dorm today',
                'hero_lead' => 'Browse verified boarding houses nationwide.',
                'search_title' => 'Find a stay',
                'search_subtitle' => 'City and budget',
                'listings_heading' => 'Available dorms',
                'listings_empty_hint' => 'Check back soon.',
                'listings_no_match_hint' => 'Try another city.',
                'footer_blurb' => 'Marketplace footer copy.',
            ])
            ->assertRedirect();

        $content = PlatformListingContentSettings::defaults();

        $this->assertSame('Find your dorm today', $content['hero_title']);
        $this->assertSame('Dorm', $content['brand_title_primary']);
        $this->assertSame('Hub', $content['brand_title_accent']);
        $this->assertSame('Find a room near campus', $content['brand_tagline']);
        $this->assertSame('Available dorms', $content['listings_heading']);
    }

    public function test_public_home_renders_custom_hero_title(): void
    {
        PlatformListingContentSettings::update([
            'brand_title_primary' => 'Stay',
            'brand_title_accent' => 'Ph',
            'brand_tagline' => 'Boarding search',
            'hero_title' => 'Custom hero headline',
            'hero_lead' => 'Custom hero subtext.',
            'search_title' => 'Search',
            'search_subtitle' => '',
            'listings_heading' => 'Listings',
            'listings_empty_hint' => 'Empty',
            'listings_no_match_hint' => 'No match',
            'footer_blurb' => 'Footer',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Custom hero headline', false)
            ->assertSee('Custom hero subtext.', false);
    }

    public function test_super_admin_can_upload_multiple_hero_images(): void
    {
        $files = [
            UploadedFile::fake()->image('hero-a.jpg', 1600, 900),
            UploadedFile::fake()->image('hero-b.jpg', 1600, 900),
        ];

        $this->actingAs($this->superAdmin())
            ->post(route('platform.settings.listing-content.update'), [
                'brand_title_primary' => 'Dorm',
                'brand_title_accent' => 'AI',
                'brand_tagline' => '',
                'hero_title' => 'Title',
                'hero_lead' => 'Lead text for hero.',
                'search_title' => 'Search',
                'search_subtitle' => '',
                'listings_heading' => 'Listings',
                'listings_empty_hint' => '',
                'listings_no_match_hint' => '',
                'footer_blurb' => '',
                'hero_images' => $files,
            ])
            ->assertRedirect();

        $paths = PlatformListingContentSettings::heroImagePaths();
        $this->assertGreaterThanOrEqual(2, count($paths));
        $uploaded = collect($paths)->first(fn (string $path) => str_starts_with($path, 'marketplace/'));
        $this->assertNotNull($uploaded);
        Storage::disk('public')->assertExists($uploaded);
    }

    public function test_public_home_shows_primary_hero_when_multiple_slides_configured(): void
    {
        $organization = Organization::query()
            ->where('slug', config('dormai.platform_organization_slug'))
            ->firstOrFail();

        $settings = $organization->settings ?? [];
        $stored = $settings[PlatformListingContentSettings::SETTINGS_KEY] ?? [];
        $stored['hero_images'] = [
            'https://example.com/a.jpg',
            'https://example.com/b.jpg',
        ];
        unset($stored['hero_image']);
        $settings[PlatformListingContentSettings::SETTINGS_KEY] = $stored;
        $organization->update(['settings' => $settings]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-da-hero-slideshow', false)
            ->assertSee('https://example.com/a.jpg', false)
            ->assertSee('https://example.com/b.jpg', false);
    }
}
