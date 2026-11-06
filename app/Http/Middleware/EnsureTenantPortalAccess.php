<?php

namespace App\Http\Middleware;

use App\Enums\OrganizationStatus;
use App\Enums\TenantStatus;
use App\Enums\UserAccountType;
use App\Support\Tenancy\OrganizationContext;
use App\Support\Tenancy\TenantPortalContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPortalAccess
{
    public function __construct(
        private readonly TenantPortalContext $tenantPortalContext,
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $this->organizationContext->resolveForUser($user);
            $this->tenantPortalContext->resolveForUser($user);
        }

        if ($user === null || $user->account_type !== UserAccountType::Tenant) {
            abort(403, 'Tenant portal access is required.');
        }

        if (! $this->tenantPortalContext->hasTenant()) {
            abort(403, 'Your account is not linked to a tenant profile.');
        }

        $tenant = $this->tenantPortalContext->ensureTenant();

        if ($tenant->status !== TenantStatus::Active) {
            abort(403, 'Your tenant profile is not active.');
        }

        $organization = $this->organizationContext->organization();

        if ($organization === null || $organization->status !== OrganizationStatus::Active) {
            abort(403, 'Your organization is not active.');
        }

        return $next($request);
    }
}
