<?php

namespace Database\Factories;

use App\Enums\FloorStatus;
use App\Models\Building;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Floor> */
class FloorFactory extends Factory
{
    protected $model = Floor::class;

    public function definition(): array
    {
        return [
            'organization_id' => fn (array $attributes) => Building::find($attributes['building_id'])->organization_id,
            'building_id' => Building::factory(),
            'name' => 'Floor 1',
            'level_number' => 1,
            'status' => FloorStatus::Active,
        ];
    }
}
