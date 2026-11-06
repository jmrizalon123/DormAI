<?php

namespace App\Http\Controllers\Platform;

use App\Enums\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\Platform\PlatformContactService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(PlatformContactService $contactService): View
    {
        return view('platform.dashboard', [
            'stats' => [
                'organizations_total' => Organization::query()->count(),
                'organizations_pending' => Organization::query()->where('status', OrganizationStatus::Pending)->count(),
                'organizations_active' => Organization::query()->where('status', OrganizationStatus::Active)->count(),
                'users_total' => User::query()->whereNotNull('organization_id')->count(),
                'contact_pending' => $contactService->pendingCount(),
                'contact_open' => $contactService->openInboxCount(),
            ],
        ]);
    }
}
