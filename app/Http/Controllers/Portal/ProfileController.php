<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant()->load('emergencyContacts');

        return view('portal.profile.edit', compact('tenant'));
    }

    public function update(Request $request, TenantPortalContext $tenantPortalContext): RedirectResponse
    {
        $tenant = $tenantPortalContext->ensureTenant();

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:32'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'school_or_work' => ['nullable', 'string', 'max:160'],
        ]);

        $tenant->update($validated);

        return back()->with('success', 'Profile updated.');
    }
}
