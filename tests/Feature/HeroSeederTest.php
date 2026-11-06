<?php

namespace Tests\Feature;

use App\Support\Platform\PlatformListingContentSettings;
use Database\Seeders\HeroSeeder;
use Database\Seeders\PlatformOrganizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_hero_seeder_publishes_images_and_stores_slideshow_paths(): void
    {
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(HeroSeeder::class);

        $paths = PlatformListingContentSettings::heroImagePaths();

        $this->assertGreaterThanOrEqual(3, count($paths));
        $this->assertContains('images/marketplace-hero.jpg', $paths);

        foreach ($paths as $path) {
            $this->assertFileExists(public_path($path), "Expected public hero file: {$path}");
        }

        $urls = PlatformListingContentSettings::heroImageUrls();
        $this->assertSame(count($paths), count($urls));
    }
}
