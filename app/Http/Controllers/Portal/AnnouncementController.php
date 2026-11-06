<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant();

        $announcements = Announcement::query()
            ->where('organization_id', $tenant->organization_id)
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('portal.announcements.index', compact('announcements'));
    }

    public function show(Announcement $portalAnnouncement): View
    {
        return view('portal.announcements.show', ['announcement' => $portalAnnouncement]);
    }
}
