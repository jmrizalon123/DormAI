<?php

namespace App\Support\Routing;

use App\Models\Announcement;
use App\Models\BedSpace;
use App\Models\Building;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomAmenity;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UtilityMeter;
use App\Models\UtilityRate;
use App\Models\UtilityReading;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class OrganizationScopedBindings
{
    public static function register(): void
    {
        self::bind('announcement', Announcement::class);
        self::bind('property', Property::class);
        self::bind('building', Building::class);
        self::bind('floor', Floor::class);
        self::bind('room', Room::class);
        self::bind('bedSpace', BedSpace::class);
        self::bind('roomType', RoomType::class);
        self::bind('roomAmenity', RoomAmenity::class);
        self::bind('tenant', Tenant::class);
        self::bind('reservation', Reservation::class);
        self::bind('contract', Contract::class);
        self::bind('contractTemplate', ContractTemplate::class);
        self::bind('invoice', Invoice::class);
        self::bind('payment', Payment::class);
        self::bind('expenseCategory', ExpenseCategory::class);
        self::bind('expense', Expense::class);
        self::bind('utilityRate', UtilityRate::class);
        self::bind('utilityMeter', UtilityMeter::class);
        self::bind('utilityReading', UtilityReading::class);
        self::bind('maintenanceRequest', MaintenanceRequest::class);
        self::bind('workOrder', WorkOrder::class);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private static function bind(string $key, string $modelClass): void
    {
        Route::bind($key, function (string $value) use ($modelClass) {
            $query = $modelClass::query()->whereKey($value);

            $organizationId = self::tryResolveOrganizationId();

            if ($organizationId !== null) {
                $query->where('organization_id', $organizationId);
            }

            return $query->firstOrFail();
        });
    }

    private static function tryResolveOrganizationId(): ?int
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user === null || $user->organization_id === null) {
            return null;
        }

        return (int) $user->organization_id;
    }
}
