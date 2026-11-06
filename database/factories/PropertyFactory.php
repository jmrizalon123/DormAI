<?php

namespace Database\Factories;

use App\Enums\PropertyStatus;
use App\Models\Organization;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Property> */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company().' Boarding House',
            'code' => strtoupper(fake()->unique()->bothify('??###')),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'province' => 'Metro Manila',
            'country' => 'PH',
            'status' => PropertyStatus::Active,
        ];
    }
}
