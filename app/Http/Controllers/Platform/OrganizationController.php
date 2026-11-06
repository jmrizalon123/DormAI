<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\RejectOrganizationRequest;
use App\Models\Organization;
use App\Services\Organization\OrganizationApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Organization::class);

        $organizations = Organization::query()
            ->withCount('users')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('platform.organizations.index', compact('organizations'));
    }

    public function show(Organization $organization): View
    {
        $this->authorize('view', $organization);

        $organization->load(['users' => fn ($query) => $query->latest()]);

        return view('platform.organizations.show', compact('organization'));
    }

    public function approve(Organization $organization, OrganizationApprovalService $approvalService): RedirectResponse
    {
        $this->authorize('approve', $organization);

        $approvalService->approve($organization, request()->user());

        return back()->with('success', 'Organization approved successfully.');
    }

    public function suspend(Organization $organization, OrganizationApprovalService $approvalService): RedirectResponse
    {
        $this->authorize('suspend', $organization);

        $approvalService->suspend($organization, request()->user(), request('reason'));

        return back()->with('success', 'Organization suspended.');
    }

    public function reject(
        RejectOrganizationRequest $request,
        Organization $organization,
        OrganizationApprovalService $approvalService,
    ): RedirectResponse {
        $approvalService->reject(
            $organization,
            $request->user(),
            $request->validated('rejection_reason'),
        );

        return back()->with('success', 'Organization rejected.');
    }
}
