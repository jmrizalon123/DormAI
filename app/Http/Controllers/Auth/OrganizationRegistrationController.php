<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreOrganizationRegistrationRequest;
use App\Services\Organization\OrganizationRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrganizationRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-organization');
    }

    public function store(
        StoreOrganizationRegistrationRequest $request,
        OrganizationRegistrationService $registrationService,
    ): RedirectResponse {
        $user = $registrationService->register($request->validated());

        $user->sendEmailVerificationNotification();

        return redirect()
            ->route('login')
            ->with('status', 'registration-submitted');
    }
}
