<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlatformOrganizationSeeder::class,
            HeroSeeder::class,
            RolesAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            TenantSeeder::class,
        ]);
    }
}
