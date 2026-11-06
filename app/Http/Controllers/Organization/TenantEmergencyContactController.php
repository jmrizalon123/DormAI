<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantEmergencyContact;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantEmergencyContactController extends Controller
{
    public function store(Request $request, Tenant $tenant, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['is_primary'])) {
            $tenant->emergencyContacts()->update(['is_primary' => false]);
        }

        $tenant->emergencyContacts()->create([
            ...$validated,
            'organization_id' => $organizationContext->ensureId(),
            'is_primary' => (bool) ($validated['is_primary'] ?? false),
        ]);

        return back()->with('success', 'Emergency contact added.');
    }

    public function destroy(Tenant $tenant, TenantEmergencyContact $contact): RedirectResponse
    {
        $this->authorize('update', $tenant);

        if ((int) $contact->tenant_id !== (int) $tenant->id
            || (int) $contact->organization_id !== (int) $tenant->organization_id) {
            abort(404);
        }

        $contact->delete();

        return back()->with('success', 'Emergency contact removed.');
    }
}
