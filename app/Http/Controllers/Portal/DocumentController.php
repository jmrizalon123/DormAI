<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\TenantDocument;
use App\Support\Tenancy\TenantPortalContext;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(TenantPortalContext $tenantPortalContext): View
    {
        $tenant = $tenantPortalContext->ensureTenant();

        $documents = TenantDocument::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->get();

        return view('portal.documents.index', compact('documents', 'tenant'));
    }

    public function download(TenantDocument $portalDocument): StreamedResponse
    {
        if (! Storage::disk('local')->exists($portalDocument->path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $portalDocument->path,
            $portalDocument->original_name,
        );
    }
}
