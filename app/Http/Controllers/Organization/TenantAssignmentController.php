<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\BedSpace;
use App\Models\Tenant;
use App\Models\TenantAssignment;
use App\Services\Tenant\TenantAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantAssignmentController extends Controller
{
    public function store(Request $request, Tenant $tenant, TenantAssignmentService $assignmentService): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'bed_space_id' => ['required', 'integer', 'exists:bed_spaces,id'],
            'move_in_date' => ['required', 'date'],
        ]);

        $bedSpace = BedSpace::query()
            ->where('organization_id', $tenant->organization_id)
            ->whereKey($validated['bed_space_id'])
            ->firstOrFail();

        $assignmentService->assign($tenant, $bedSpace, $validated['move_in_date']);

        return back()->with('success', 'Tenant assigned to bed space.');
    }

    public function destroy(
        Request $request,
        Tenant $tenant,
        TenantAssignment $assignment,
        TenantAssignmentService $assignmentService,
    ): RedirectResponse {
        $this->authorize('update', $tenant);

        if ((int) $assignment->tenant_id !== (int) $tenant->id
            || (int) $assignment->organization_id !== (int) $tenant->organization_id) {
            abort(404);
        }

        $validated = $request->validate([
            'move_out_date' => ['nullable', 'date'],
            'ended_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $assignmentService->moveOut(
            $assignment,
            $validated['move_out_date'] ?? null,
            $validated['ended_reason'] ?? null,
        );

        return back()->with('success', 'Move-out recorded and bed marked available.');
    }
}
