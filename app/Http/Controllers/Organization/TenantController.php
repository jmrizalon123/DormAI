<?php

namespace App\Http\Controllers\Organization;

use App\Enums\BedSpaceStatus;
use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Models\BedSpace;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\Audit\AuditLogger;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Tenant::class);

        $organizationId = $organizationContext->ensureId();

        $tenants = Tenant::query()
            ->where('organization_id', $organizationId)
            ->with(['activeAssignment.property', 'activeAssignment.room', 'activeAssignment.bedSpace'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('property_id'), function ($query) use ($request) {
                $query->whereHas('activeAssignment', fn ($inner) => $inner->where('property_id', $request->integer('property_id')));
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        $properties = Property::query()->where('organization_id', $organizationId)->orderBy('name')->get();

        return view('organization.tenants.index', compact('tenants', 'properties'));
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        return view('organization.tenants.create');
    }

    public function store(Request $request, OrganizationContext $organizationContext, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('create', Tenant::class);

        $validated = $this->validatedTenant($request);

        $tenant = Tenant::query()->create([
            ...$validated,
            'organization_id' => $organizationContext->ensureId(),
        ]);

        $auditLogger->log('tenant.created', $tenant);

        return redirect()
            ->route('organization.tenants.show', $tenant)
            ->with('success', 'Tenant registered successfully.');
    }

    public function show(Tenant $tenant, OrganizationContext $organizationContext): View
    {
        $this->authorize('view', $tenant);

        $tenant->load([
            'user',
            'emergencyContacts',
            'documents',
            'activeAssignment.property',
            'activeAssignment.building',
            'activeAssignment.floor',
            'activeAssignment.room',
            'activeAssignment.bedSpace',
            'assignmentHistory',
        ]);

        $availableBeds = BedSpace::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->whereIn('status', [BedSpaceStatus::Available, BedSpaceStatus::Reserved])
            ->whereDoesntHave('activeAssignments')
            ->with(['room.building.property', 'room.floor'])
            ->orderBy('room_id')
            ->get();

        return view('organization.tenants.show', compact('tenant', 'availableBeds'));
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);

        return view('organization.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $tenant->update($this->validatedTenant($request));
        $auditLogger->log('tenant.updated', $tenant);

        return redirect()
            ->route('organization.tenants.show', $tenant)
            ->with('success', 'Tenant profile updated.');
    }

    public function destroy(Tenant $tenant, AuditLogger $auditLogger): RedirectResponse
    {
        $this->authorize('delete', $tenant);

        if ($tenant->assignments()->where('status', 'active')->exists()) {
            return back()->withErrors(['tenant' => 'End the active bed assignment before removing this tenant.']);
        }

        $auditLogger->log('tenant.deleted', $tenant);
        $tenant->delete();

        return redirect()
            ->route('organization.tenants.index')
            ->with('success', 'Tenant record removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedTenant(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'middle_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'school_or_work' => ['nullable', 'string', 'max:255'],
            'valid_id_type' => ['nullable', 'string', 'max:120'],
            'valid_id_number' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:'.implode(',', array_column(TenantStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
