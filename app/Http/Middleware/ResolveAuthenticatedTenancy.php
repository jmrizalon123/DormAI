<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\OrganizationContext;
use App\Support\Tenancy\PlatformOrganization;
use App\Support\Tenancy\TenantPortalContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs after auth so organization and tenant portal context match the logged-in user.
 */
class ResolveAuthenticatedTenancy
{
    public function __construct(
        private readonly OrganizationContext $organizationContext,
        private readonly TenantPortalContext $tenantPortalContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $this->organizationContext->resolveForUser($user);
            $this->tenantPortalContext->resolveForUser($user);

            if ($user->isSuperAdmin()) {
                setPermissionsTeamId(PlatformOrganization::id());
            } else {
                setPermissionsTeamId($this->organizationContext->id());
            }
        } else {
            $this->organizationContext->clear();
            $this->tenantPortalContext->clear();
            setPermissionsTeamId(null);
        }

        return $next($request);
    }
}
