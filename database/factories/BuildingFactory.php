<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Building> */
class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition(): array
    {
        return [
            'organization_id' => fn (array $attributes) => Property::find($attributes['property_id'])->organization_id,
            'property_id' => Property::factory(),
            'name' => 'Building '.fake()->randomLetter(),
            'description' => fake()->sentence(),
        ];
    }
}
