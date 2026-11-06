<?php

namespace App\Providers;

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
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PlatformContactMessage;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\TenantPaymentSubmission;
use App\Models\User;
use App\Models\UtilityMeter;
use App\Models\UtilityRate;
use App\Models\UtilityReading;
use App\Models\WorkOrder;
use App\Policies\AnnouncementPolicy;
use App\Policies\BedSpacePolicy;
use App\Policies\BuildingPolicy;
use App\Policies\ContractPolicy;
use App\Policies\ContractTemplatePolicy;
use App\Policies\ExpenseCategoryPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\FloorPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MaintenanceRequestPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PlatformContactMessagePolicy;
use App\Policies\PropertyPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\RoomPolicy;
use App\Policies\RoomTypePolicy;
use App\Policies\TenantPaymentSubmissionPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use App\Policies\UtilityMeterPolicy;
use App\Policies\UtilityRatePolicy;
use App\Policies\UtilityReadingPolicy;
use App\Policies\WorkOrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Announcement::class => AnnouncementPolicy::class,
        Organization::class => OrganizationPolicy::class,
        PlatformContactMessage::class => PlatformContactMessagePolicy::class,
        TenantPaymentSubmission::class => TenantPaymentSubmissionPolicy::class,
        User::class => UserPolicy::class,
        Property::class => PropertyPolicy::class,
        Building::class => BuildingPolicy::class,
        Floor::class => FloorPolicy::class,
        Room::class => RoomPolicy::class,
        BedSpace::class => BedSpacePolicy::class,
        RoomType::class => RoomTypePolicy::class,
        Tenant::class => TenantPolicy::class,
        Reservation::class => ReservationPolicy::class,
        Contract::class => ContractPolicy::class,
        ContractTemplate::class => ContractTemplatePolicy::class,
        Invoice::class => InvoicePolicy::class,
        Payment::class => PaymentPolicy::class,
        ExpenseCategory::class => ExpenseCategoryPolicy::class,
        Expense::class => ExpensePolicy::class,
        UtilityRate::class => UtilityRatePolicy::class,
        UtilityMeter::class => UtilityMeterPolicy::class,
        UtilityReading::class => UtilityReadingPolicy::class,
        MaintenanceRequest::class => MaintenanceRequestPolicy::class,
        WorkOrder::class => WorkOrderPolicy::class,
    ];

    public function boot(): void
    {
        Gate::define('viewFinancialReports', function (User $user): bool {
            return $user->can('view reports') || $user->can('manage expenses');
        });

        Gate::define('viewOrganizationReports', function (User $user): bool {
            return $user->can('view reports')
                || $user->can('manage expenses')
                || $user->can('manage utilities');
        });
    }
}
