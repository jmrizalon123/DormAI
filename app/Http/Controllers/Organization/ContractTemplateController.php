<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\ContractTemplate;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractTemplateController extends Controller
{
    public function index(OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', ContractTemplate::class);

        $templates = ContractTemplate::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('organization.contract-templates.index', compact('templates'));
    }

    public function create(): View
    {
        $this->authorize('create', ContractTemplate::class);

        return view('organization.contract-templates.create');
    }

    public function store(Request $request, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('create', ContractTemplate::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $organizationId = $organizationContext->ensureId();

        if ($request->boolean('is_default')) {
            ContractTemplate::query()
                ->where('organization_id', $organizationId)
                ->update(['is_default' => false]);
        }

        ContractTemplate::query()->create([
            'organization_id' => $organizationId,
            'name' => $validated['name'],
            'body' => $validated['body'],
            'is_default' => $request->boolean('is_default'),
        ]);

        return redirect()
            ->route('organization.contract-templates.index')
            ->with('success', 'Template saved.');
    }

    public function edit(ContractTemplate $contractTemplate): View
    {
        $this->authorize('update', $contractTemplate);

        return view('organization.contract-templates.edit', compact('contractTemplate'));
    }

    public function update(Request $request, ContractTemplate $contractTemplate): RedirectResponse
    {
        $this->authorize('update', $contractTemplate);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            ContractTemplate::query()
                ->where('organization_id', $contractTemplate->organization_id)
                ->where('id', '!=', $contractTemplate->id)
                ->update(['is_default' => false]);
        }

        $contractTemplate->update([
            'name' => $validated['name'],
            'body' => $validated['body'],
            'is_default' => $request->boolean('is_default'),
        ]);

        return redirect()
            ->route('organization.contract-templates.index')
            ->with('success', 'Template updated.');
    }

    public function destroy(ContractTemplate $contractTemplate): RedirectResponse
    {
        $this->authorize('delete', $contractTemplate);

        if ($contractTemplate->is_default) {
            return back()->with('error', 'Cannot delete the default template.');
        }

        $contractTemplate->delete();

        return redirect()
            ->route('organization.contract-templates.index')
            ->with('success', 'Template deleted.');
    }
}
