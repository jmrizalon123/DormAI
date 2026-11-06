<?php

namespace Tests\Feature;

use App\Enums\BedSpaceStatus;
use App\Enums\ContractStatus;
use App\Enums\ReservationStatus;
use App\Models\BedSpace;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Organization;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\Contract\ContractService;
use App\Services\Reservation\CheckInService;
use App\Services\Reservation\ReservationService;
use Database\Seeders\PlatformOrganizationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReservationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlatformOrganizationSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function ownerWithBed(): array
    {
        $organization = Organization::factory()->create(['slug' => 'sunrise-dorm']);
        $owner = User::factory()->organizationOwner()->create(['organization_id' => $organization->id]);
        setPermissionsTeamId($organization->id);
        $owner->assignRole('organization-owner');

        $property = Property::factory()->create(['organization_id' => $organization->id]);
        $building = Building::factory()->create(['organization_id' => $organization->id, 'property_id' => $property->id]);
        $floor = Floor::factory()->create(['organization_id' => $organization->id, 'building_id' => $building->id]);
        $room = Room::factory()->create([
            'organization_id' => $organization->id,
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);
        $bed = BedSpace::factory()->create(['organization_id' => $organization->id, 'room_id' => $room->id]);

        return compact('organization', 'owner', 'bed');
    }

    public function test_public_can_submit_reservation_for_available_bed(): void
    {
        ['organization' => $organization, 'bed' => $bed] = $this->ownerWithBed();

        $response = $this->post(route('public.stay.reserve', [
            'publicOrganization' => $organization->slug,
            'bedSpace' => $bed,
        ]), [
            'applicant_first_name' => 'Ana',
            'applicant_last_name' => 'Reyes',
            'applicant_email' => 'ana@example.test',
            'requested_check_in' => now()->addDay()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reservations', [
            'organization_id' => $organization->id,
            'bed_space_id' => $bed->id,
            'status' => ReservationStatus::Pending->value,
        ]);
    }

    public function test_double_booking_is_blocked(): void
    {
        ['organization' => $organization, 'bed' => $bed] = $this->ownerWithBed();

        app(ReservationService::class)->submitPublicRequest($organization, $bed, [
            'applicant_first_name' => 'First',
            'applicant_last_name' => 'Guest',
            'applicant_email' => 'first@example.test',
            'requested_check_in' => now()->addDay()->toDateString(),
        ]);

        $this->expectException(ValidationException::class);

        app(ReservationService::class)->submitPublicRequest($organization, $bed->fresh(), [
            'applicant_first_name' => 'Second',
            'applicant_last_name' => 'Guest',
            'applicant_email' => 'second@example.test',
            'requested_check_in' => now()->addDays(2)->toDateString(),
        ]);
    }

    public function test_approve_marks_bed_reserved_and_check_in_assigns_tenant(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'bed' => $bed] = $this->ownerWithBed();

        $reservation = app(ReservationService::class)->submitPublicRequest($organization, $bed, [
            'applicant_first_name' => 'Mia',
            'applicant_last_name' => 'Santos',
            'applicant_email' => 'mia@example.test',
            'requested_check_in' => now()->toDateString(),
        ]);

        $this->actingAs($owner);
        app(ReservationService::class)->approve($reservation->fresh(), $owner);

        $this->assertSame(BedSpaceStatus::Reserved, $bed->fresh()->status);

        $result = app(CheckInService::class)->checkIn($reservation->fresh());

        $this->assertSame(ReservationStatus::CheckedIn, $result['reservation']->status);
        $this->assertSame(BedSpaceStatus::Occupied, $bed->fresh()->status);
        $this->assertDatabaseHas('contracts', [
            'tenant_id' => $result['tenant']->id,
            'reservation_id' => $reservation->id,
        ]);
    }

    public function test_owner_cannot_view_other_organization_reservation(): void
    {
        ['owner' => $owner] = $this->ownerWithBed();

        $otherOrg = Organization::factory()->create();
        $property = Property::factory()->create(['organization_id' => $otherOrg->id]);
        $building = Building::factory()->create(['organization_id' => $otherOrg->id, 'property_id' => $property->id]);
        $floor = Floor::factory()->create(['organization_id' => $otherOrg->id, 'building_id' => $building->id]);
        $room = Room::factory()->create([
            'organization_id' => $otherOrg->id,
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
        ]);
        $bed = BedSpace::factory()->create(['organization_id' => $otherOrg->id, 'room_id' => $room->id]);

        $other = Reservation::query()->create([
            'organization_id' => $otherOrg->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'bed_space_id' => $bed->id,
            'reference_code' => 'RES-OTHER-001',
            'applicant_first_name' => 'Other',
            'applicant_last_name' => 'Guest',
            'applicant_email' => 'other@example.test',
            'requested_check_in' => now()->addDay(),
            'status' => ReservationStatus::Pending,
        ]);

        $this->actingAs($owner)
            ->get(route('organization.reservations.show', $other))
            ->assertNotFound();
    }

    public function test_owner_can_approve_and_reject_via_http(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'bed' => $bed] = $this->ownerWithBed();

        $reservation = app(ReservationService::class)->submitPublicRequest($organization, $bed, [
            'applicant_first_name' => 'Leo',
            'applicant_last_name' => 'Lim',
            'applicant_email' => 'leo@example.test',
            'requested_check_in' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($owner)
            ->post(route('organization.reservations.approve', $reservation))
            ->assertRedirect();

        $this->assertSame(BedSpaceStatus::Reserved, $bed->fresh()->status);

        $secondBed = BedSpace::factory()->create([
            'organization_id' => $organization->id,
            'room_id' => $bed->room_id,
            'label' => 'Bed B',
        ]);

        $reservation2 = app(ReservationService::class)->submitPublicRequest($organization, $secondBed, [
            'applicant_first_name' => 'Kim',
            'applicant_last_name' => 'Tan',
            'applicant_email' => 'kim@example.test',
            'requested_check_in' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($owner)
            ->post(route('organization.reservations.reject', $reservation2), [
                'rejection_reason' => 'Dates not available',
            ])
            ->assertRedirect();

        $this->assertSame(ReservationStatus::Rejected, $reservation2->fresh()->status);
    }

    public function test_rejecting_approved_reservation_frees_bed(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'bed' => $bed] = $this->ownerWithBed();

        $reservation = app(ReservationService::class)->submitPublicRequest($organization, $bed, [
            'applicant_first_name' => 'A',
            'applicant_last_name' => 'B',
            'applicant_email' => 'ab@example.test',
            'requested_check_in' => now()->addDay()->toDateString(),
        ]);

        app(ReservationService::class)->approve($reservation->fresh(), $owner);
        app(ReservationService::class)->reject($reservation->fresh(), $owner, 'Guest cancelled');

        $this->assertSame(BedSpaceStatus::Available, $bed->fresh()->status);
    }

    public function test_check_in_blocked_until_deposit_is_paid(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'bed' => $bed] = $this->ownerWithBed();

        $reservation = app(ReservationService::class)->submitPublicRequest($organization, $bed, [
            'applicant_first_name' => 'Dep',
            'applicant_last_name' => 'Osit',
            'applicant_email' => 'dep@example.test',
            'requested_check_in' => now()->toDateString(),
            'deposit_amount' => 1500,
        ]);

        app(ReservationService::class)->approve($reservation->fresh(), $owner);

        $this->expectException(ValidationException::class);
        app(CheckInService::class)->checkIn($reservation->fresh());
    }

    public function test_expire_command_marks_overdue_pending_reservations(): void
    {
        ['organization' => $organization, 'bed' => $bed] = $this->ownerWithBed();

        $reservation = app(ReservationService::class)->submitPublicRequest($organization, $bed, [
            'applicant_first_name' => 'Old',
            'applicant_last_name' => 'Request',
            'applicant_email' => 'old@example.test',
            'requested_check_in' => now()->addDay()->toDateString(),
        ]);

        $reservation->update(['expires_at' => now()->subHour()]);

        Artisan::call('dormai:expire-reservations');

        $this->assertSame(ReservationStatus::Expired, $reservation->fresh()->status);
    }

    public function test_contract_can_be_renewed(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'bed' => $bed] = $this->ownerWithBed();

        $reservation = app(ReservationService::class)->submitPublicRequest($organization, $bed, [
            'applicant_first_name' => 'Renew',
            'applicant_last_name' => 'Me',
            'applicant_email' => 'renew@example.test',
            'requested_check_in' => now()->toDateString(),
        ]);

        app(ReservationService::class)->approve($reservation->fresh(), $owner);
        $result = app(CheckInService::class)->checkIn($reservation->fresh());

        $renewed = app(ContractService::class)->renew(
            $result['contract'],
            now()->addYear()->toDateString(),
            4000,
        );

        $this->assertDatabaseHas('contracts', [
            'id' => $renewed->id,
            'previous_contract_id' => $result['contract']->id,
            'monthly_rate' => 4000,
        ]);
        $this->assertSame(ContractStatus::Renewed, $result['contract']->fresh()->status);
    }

    public function test_owner_can_view_contract_print_route(): void
    {
        ['organization' => $organization, 'owner' => $owner, 'bed' => $bed] = $this->ownerWithBed();

        $reservation = app(ReservationService::class)->submitPublicRequest($organization, $bed, [
            'applicant_first_name' => 'Print',
            'applicant_last_name' => 'Test',
            'applicant_email' => 'print@example.test',
            'requested_check_in' => now()->toDateString(),
        ]);

        app(ReservationService::class)->approve($reservation->fresh(), $owner);
        $contract = app(CheckInService::class)->checkIn($reservation->fresh())['contract'];

        $this->actingAs($owner)
            ->get(route('organization.contracts.print', $contract))
            ->assertOk();
    }
}
