<?php

namespace Database\Seeders;

use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use App\Models\User;
use App\Support\Tenancy\PlatformOrganization;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@dormai.test');
        $password = env('SUPER_ADMIN_PASSWORD', 'password');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'DormAI Super Admin',
                'password' => $password,
                'organization_id' => null,
                'account_type' => UserAccountType::SuperAdmin,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        setPermissionsTeamId(PlatformOrganization::id());
        $user->syncRoles(['super-admin']);
    }
}
