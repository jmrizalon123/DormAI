<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Portal\TenantPortalAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TenantPortalAccessController extends Controller
{
    public function store(
        Request $request,
        Tenant $tenant,
        TenantPortalAccessService $portalAccessService,
    ): RedirectResponse {
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $portalAccessService->enable(
                $tenant,
                $validated['email'],
                $validated['password'],
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Tenant portal access enabled.');
    }
}
