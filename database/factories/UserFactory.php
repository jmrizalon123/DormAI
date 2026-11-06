<?php

namespace Database\Factories;

use App\Enums\UserAccountType;
use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'account_type' => UserAccountType::Staff,
            'status' => UserStatus::Active,
            'phone' => fake()->phoneNumber(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => null,
            'account_type' => UserAccountType::SuperAdmin,
        ]);
    }

    public function organizationOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => UserAccountType::OrganizationOwner,
        ]);
    }
}
