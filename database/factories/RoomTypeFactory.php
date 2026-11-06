<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoomType> */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->randomElement(['Standard', 'Deluxe', 'Shared']),
            'description' => fake()->sentence(),
        ];
    }
}
