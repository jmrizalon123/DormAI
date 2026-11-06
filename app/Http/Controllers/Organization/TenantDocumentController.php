<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDocument;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantDocumentController extends Controller
{
    public function store(Request $request, Tenant $tenant, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'category' => ['required', 'in:valid_id,contract,other'],
            'document' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $file = $validated['document'];
        $path = $file->store(
            'tenant-documents/'.$organizationContext->ensureId().'/'.$tenant->id,
            'local',
        );

        TenantDocument::query()->create([
            'organization_id' => $organizationContext->ensureId(),
            'tenant_id' => $tenant->id,
            'category' => $validated['category'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'Document uploaded securely.');
    }

    public function show(Tenant $tenant, TenantDocument $document): StreamedResponse
    {
        $this->authorize('view', $tenant);

        if ((int) $document->tenant_id !== (int) $tenant->id
            || (int) $document->organization_id !== (int) $tenant->organization_id) {
            abort(404);
        }

        return Storage::disk('local')->download($document->path, $document->original_name);
    }

    public function destroy(Tenant $tenant, TenantDocument $document): RedirectResponse
    {
        $this->authorize('update', $tenant);

        if ((int) $document->tenant_id !== (int) $tenant->id
            || (int) $document->organization_id !== (int) $tenant->organization_id) {
            abort(404);
        }

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}
