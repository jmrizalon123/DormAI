<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\OrganizationContext;
use App\Support\Tenancy\PlatformOrganization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganizationContext
{
    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $this->organizationContext->resolveForUser($user);

            if ($user->isSuperAdmin()) {
                setPermissionsTeamId(PlatformOrganization::id());
            } else {
                setPermissionsTeamId($this->organizationContext->id());
            }
        } else {
            $this->organizationContext->clear();
            setPermissionsTeamId(null);
        }

        return $next($request);
    }
}
