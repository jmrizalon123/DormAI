<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Room> */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'organization_id' => fn (array $attributes) => Floor::find($attributes['floor_id'])->organization_id,
            'property_id' => fn (array $attributes) => Floor::find($attributes['floor_id'])->building->property_id,
            'building_id' => fn (array $attributes) => Floor::find($attributes['floor_id'])->building_id,
            'floor_id' => Floor::factory(),
            'room_number' => (string) fake()->numberBetween(101, 999),
            'capacity' => 4,
            'monthly_rate' => 3500,
            'status' => RoomStatus::Available,
        ];
    }
}
