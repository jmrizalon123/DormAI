<?php

namespace App\Http\Controllers;

use App\Support\Tenancy\TenantPortalContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('platform.dashboard');
        }

        if ($user->isTenant()) {
            $portalContext = app(TenantPortalContext::class);
            $portalContext->resolveForUser($user);

            if ($portalContext->hasTenant()) {
                return redirect()->route('portal.dashboard');
            }
        }

        if ($user->organization_id !== null) {
            return redirect()->route('organization.dashboard');
        }

        abort(403, 'Your account does not have an assigned dashboard.');
    }
}
