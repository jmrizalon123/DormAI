<?php

namespace App\Support\Routing;

use App\Models\Announcement;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use App\Models\TenantDocument;
use App\Models\TenantPaymentSubmission;
use App\Models\User;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class PortalScopedBindings
{
    public static function register(): void
    {
        Route::bind('portalInvoice', function (string $value) {
            $tenant = self::resolvePortalTenant();

            return Invoice::query()
                ->where('organization_id', $tenant->organization_id)
                ->where('tenant_id', $tenant->id)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('portalMaintenanceRequest', function (string $value) {
            $tenant = self::resolvePortalTenant();

            return MaintenanceRequest::query()
                ->where('organization_id', $tenant->organization_id)
                ->where('tenant_id', $tenant->id)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('portalAnnouncement', function (string $value) {
            $tenant = self::resolvePortalTenant();

            return Announcement::query()
                ->where('organization_id', $tenant->organization_id)
                ->where('is_published', true)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('portalContract', function (string $value) {
            $tenant = self::resolvePortalTenant();

            return Contract::query()
                ->where('organization_id', $tenant->organization_id)
                ->where('tenant_id', $tenant->id)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('portalDocument', function (string $value) {
            $tenant = self::resolvePortalTenant();

            return TenantDocument::query()
                ->where('organization_id', $tenant->organization_id)
                ->where('tenant_id', $tenant->id)
                ->whereKey($value)
                ->firstOrFail();
        });

        Route::bind('paymentSubmission', function (string $value) {
            $user = auth()->user();
            $organizationId = $user?->organization_id;

            $query = TenantPaymentSubmission::query()->whereKey($value);

            if ($organizationId !== null) {
                $query->where('organization_id', $organizationId);
            }

            return $query->firstOrFail();
        });
    }

    private static function resolvePortalTenant(): Tenant
    {
        $context = app(TenantPortalContext::class);

        if ($context->hasTenant()) {
            return $context->ensureTenant();
        }

        /** @var User|null $user */
        $user = Auth::user();

        if ($user === null || ! $user->isTenant()) {
            abort(404);
        }

        return Tenant::query()
            ->where('organization_id', $user->organization_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
    }
}
