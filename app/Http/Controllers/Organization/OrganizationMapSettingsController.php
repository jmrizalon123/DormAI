<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Support\Organization\OrganizationMapSettings;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationMapSettingsController extends Controller
{
    public function edit(OrganizationContext $organizationContext): View
    {
        abort_unless(auth()->user()?->can('manage organization settings'), 403);

        $organization = $organizationContext->ensureOrganization();
        $map = OrganizationMapSettings::forOrganization($organization);
        $system = OrganizationMapSettings::systemDefaults();

        return view('organization.settings.map', compact('organization', 'map', 'system'));
    }

    public function update(Request $request, OrganizationContext $organizationContext): RedirectResponse
    {
        abort_unless(auth()->user()?->can('manage organization settings'), 403);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'zoom' => ['required', 'integer', 'min:3', 'max:18'],
            'label' => ['nullable', 'string', 'max:120'],
        ]);

        $organization = $organizationContext->ensureOrganization();

        OrganizationMapSettings::update($organization, [
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'zoom' => (int) $validated['zoom'],
            'label' => $validated['label'] ?? OrganizationMapSettings::systemDefaults()['label'],
        ]);

        return back()->with('success', 'Default map location saved.');
    }

    public function reset(OrganizationContext $organizationContext): RedirectResponse
    {
        abort_unless(auth()->user()?->can('manage organization settings'), 403);

        OrganizationMapSettings::resetToSystemDefaults($organizationContext->ensureOrganization());

        return back()->with('success', 'Map defaults reset to system standard (Catbalogan City, Samar).');
    }
}
