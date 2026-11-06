<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantPortalContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantPortalContext
{
    public function __construct(
        private readonly TenantPortalContext $tenantPortalContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $this->tenantPortalContext->resolveForUser($user);
        } else {
            $this->tenantPortalContext->clear();
        }

        return $next($request);
    }
}
