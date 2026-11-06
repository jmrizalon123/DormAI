<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\TenantPaymentSubmission;
use App\Services\Portal\TenantPaymentSubmissionService;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantPaymentSubmissionController extends Controller
{
    public function index(Request $request, OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', TenantPaymentSubmission::class);

        $submissions = TenantPaymentSubmission::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->with(['tenant', 'invoice', 'submitter'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('organization.billing.payment-submissions.index', compact('submissions'));
    }

    public function show(TenantPaymentSubmission $paymentSubmission): View
    {
        $this->authorize('view', $paymentSubmission);

        $paymentSubmission->load(['tenant', 'invoice', 'submitter', 'reviewer']);

        return view('organization.billing.payment-submissions.show', compact('paymentSubmission'));
    }

    public function approve(
        TenantPaymentSubmission $paymentSubmission,
        Request $request,
        TenantPaymentSubmissionService $submissionService,
    ): RedirectResponse {
        $this->authorize('review', $paymentSubmission);

        try {
            $submissionService->approve($paymentSubmission, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Submission approved and payment recorded.');
    }

    public function reject(
        Request $request,
        TenantPaymentSubmission $paymentSubmission,
        TenantPaymentSubmissionService $submissionService,
    ): RedirectResponse {
        $this->authorize('review', $paymentSubmission);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        try {
            $submissionService->reject($paymentSubmission, $request->user(), $validated['reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Submission rejected.');
    }
}
