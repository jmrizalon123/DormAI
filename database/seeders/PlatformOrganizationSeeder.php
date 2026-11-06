<?php

namespace Database\Seeders;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class PlatformOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        Organization::query()->firstOrCreate(
            ['slug' => config('dormai.platform_organization_slug')],
            [
                'name' => 'DormAI Platform',
                'email' => 'platform@dormai.test',
                'status' => OrganizationStatus::Active,
                'approved_at' => now(),
            ],
        );
    }
}
