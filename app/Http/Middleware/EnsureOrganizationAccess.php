<?php

namespace App\Http\Middleware;

use App\Enums\OrganizationStatus;
use App\Enums\UserAccountType;
use App\Support\Tenancy\OrganizationContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationAccess
{
    public function __construct(
        private readonly OrganizationContext $organizationContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $this->organizationContext->resolveForUser($user);
        }

        if ($user === null || $user->isSuperAdmin()) {
            abort(403, 'Organization access is required.');
        }

        if ($user->account_type === UserAccountType::Tenant) {
            abort(403, 'Staff access only. Please use the tenant portal.');
        }

        if ($user->organization_id === null || ! $this->organizationContext->hasOrganization()) {
            abort(403, 'You are not assigned to an organization.');
        }

        $organization = $this->organizationContext->organization();

        if ($organization === null || $organization->status !== OrganizationStatus::Active) {
            abort(403, 'Your organization is not active.');
        }

        return $next($request);
    }
}
