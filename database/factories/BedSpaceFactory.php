<?php

namespace Database\Factories;

use App\Enums\BedSpaceStatus;
use App\Models\BedSpace;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BedSpace> */
class BedSpaceFactory extends Factory
{
    protected $model = BedSpace::class;

    public function definition(): array
    {
        return [
            'organization_id' => fn (array $attributes) => Room::find($attributes['room_id'])->organization_id,
            'room_id' => Room::factory(),
            'label' => 'A',
            'monthly_rate' => 3500,
            'status' => BedSpaceStatus::Available,
        ];
    }
}
