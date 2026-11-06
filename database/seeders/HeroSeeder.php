<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Support\Platform\PlatformListingContentSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class HeroSeeder extends Seeder
{
    /**
     * @var list<string> Filenames in database/seeders/assets/marketplace-hero/
     */
    private const HERO_FILES = [
        'marketplace-hero.jpg',
        'marketplace-hero-2.jpg',
        'marketplace-hero-3.jpg',
    ];

    public function run(): void
    {
        $organization = Organization::query()
            ->where('slug', config('dormai.platform_organization_slug'))
            ->first();

        if ($organization === null) {
            $this->command?->warn('Platform organization missing; run PlatformOrganizationSeeder first.');

            return;
        }

        $heroPaths = $this->publishHeroImages();
        $defaults = PlatformListingContentSettings::configDefaults();

        $settings = $organization->settings ?? [];
        $settings[PlatformListingContentSettings::SETTINGS_KEY] = array_merge(
            $defaults,
            ['hero_images' => $heroPaths],
        );

        $organization->update(['settings' => $settings]);

        $this->command?->info(sprintf(
            'Seeded marketplace hero slideshow with %d image(s).',
            count($heroPaths),
        ));
    }

    /**
     * Copy bundled hero JPGs into public/images for the marketplace slideshow.
     *
     * @return list<string> Paths relative to public/
     */
    private function publishHeroImages(): array
    {
        $sourceDir = database_path('seeders/assets/marketplace-hero');
        $targetDir = public_path('images');
        File::ensureDirectoryExists($targetDir);

        $paths = [];

        foreach (self::HERO_FILES as $filename) {
            $source = $sourceDir.DIRECTORY_SEPARATOR.$filename;
            $target = $targetDir.DIRECTORY_SEPARATOR.$filename;

            if (is_file($source)) {
                if (! is_file($target) || filemtime($source) > filemtime($target)) {
                    File::copy($source, $target);
                }
                $paths[] = 'images/'.$filename;

                continue;
            }

            if (is_file($target)) {
                $paths[] = 'images/'.$filename;

                continue;
            }

            $this->command?->warn("Hero asset missing: {$filename}");
        }

        if ($paths === []) {
            return PlatformListingContentSettings::configDefaults()['hero_images'];
        }

        return $paths;
    }
}
