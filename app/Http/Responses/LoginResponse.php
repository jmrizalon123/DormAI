<?php

namespace App\Http\Responses;

use App\Support\Tenancy\TenantPortalContext;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isSuperAdmin()) {
            return redirect()->intended(route('platform.dashboard'));
        }

        if ($user !== null && $user->isTenant()) {
            $portalContext = app(TenantPortalContext::class);
            $portalContext->resolveForUser($user);

            if ($portalContext->hasTenant()) {
                return redirect()->intended(route('portal.dashboard'));
            }
        }

        if ($user !== null && $user->organization_id !== null) {
            return redirect()->intended(route('organization.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }
}
