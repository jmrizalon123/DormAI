<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\Billing\BillingSettings;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingSettingsController extends Controller
{
    public function edit(OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Invoice::class);

        $organization = $organizationContext->organization();
        $billing = BillingSettings::forOrganization($organization);

        return view('organization.billing.settings', compact('organization', 'billing'));
    }

    public function update(Request $request, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $validated = $request->validate([
            'due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'grace_days' => ['required', 'integer', 'min:0', 'max:30'],
            'auto_generate_monthly' => ['sometimes', 'boolean'],
        ]);

        $organization = $organizationContext->organization();
        BillingSettings::update($organization, [
            'due_day' => (int) $validated['due_day'],
            'invoice_prefix' => strtoupper($validated['invoice_prefix']),
            'grace_days' => (int) $validated['grace_days'],
            'auto_generate_monthly' => $request->boolean('auto_generate_monthly'),
        ]);

        return back()->with('success', 'Billing settings saved.');
    }
}
