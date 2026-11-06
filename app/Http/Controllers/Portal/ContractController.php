<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant();

        $contracts = Contract::query()
            ->where('tenant_id', $tenant->id)
            ->with(['property', 'room'])
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('portal.contracts.index', compact('contracts', 'tenant'));
    }

    public function show(Contract $portalContract): View
    {
        $portalContract->load(['property', 'room', 'bedSpace']);

        return view('portal.contracts.show', ['contract' => $portalContract]);
    }
}
