<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\BedSpace;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'bed_space_id' => BedSpace::factory(),
            'reference_code' => 'RES-'.Str::upper(Str::random(8)),
            'applicant_first_name' => fake()->firstName(),
            'applicant_last_name' => fake()->lastName(),
            'applicant_email' => fake()->safeEmail(),
            'requested_check_in' => now()->addWeek(),
            'status' => ReservationStatus::Pending,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Reservation $reservation) {
            if ($reservation->organization_id && ! $reservation->property_id) {
                return;
            }
        });
    }
}
