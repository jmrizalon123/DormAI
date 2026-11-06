<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\Contract\ContractService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractController extends Controller
{
    public function index(OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Contract::class);

        $contracts = Contract::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->with(['tenant', 'property', 'room', 'bedSpace'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('organization.contracts.index', compact('contracts'));
    }

    public function show(Contract $contract): View
    {
        $this->authorize('view', $contract);

        $contract->load(['tenant', 'property', 'room', 'bedSpace', 'reservation', 'template']);

        return view('organization.contracts.show', compact('contract'));
    }

    public function print(Contract $contract): View
    {
        $this->authorize('view', $contract);

        $contract->load(['tenant', 'property', 'room', 'bedSpace']);

        return view('organization.contracts.print', compact('contract'));
    }

    public function download(Contract $contract): StreamedResponse
    {
        $this->authorize('view', $contract);

        if ($contract->pdf_path === null || ! Storage::disk('local')->exists($contract->pdf_path)) {
            abort(404);
        }

        $filename = $contract->contract_number.'.html';

        return Storage::disk('local')->download($contract->pdf_path, $filename, [
            'Content-Type' => 'text/html',
        ]);
    }

    public function renew(Request $request, Contract $contract, ContractService $contractService): RedirectResponse
    {
        $this->authorize('update', $contract);

        $validated = $request->validate([
            'end_date' => ['required', 'date', 'after:today'],
            'monthly_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $renewed = $contractService->renew(
            $contract,
            $validated['end_date'],
            isset($validated['monthly_rate']) ? (float) $validated['monthly_rate'] : null,
        );

        return redirect()
            ->route('organization.contracts.show', $renewed)
            ->with('success', 'Contract renewed.');
    }
}
