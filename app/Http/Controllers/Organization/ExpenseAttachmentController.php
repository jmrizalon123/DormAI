<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseAttachment;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseAttachmentController extends Controller
{
    public function store(Request $request, Expense $expense, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('attach', $expense);

        $validated = $request->validate([
            'attachment' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $file = $validated['attachment'];
        $path = $file->store(
            'expense-attachments/'.$organizationContext->ensureId().'/'.$expense->id,
            'local',
        );

        ExpenseAttachment::query()->create([
            'organization_id' => $expense->organization_id,
            'expense_id' => $expense->id,
            'uploaded_by' => $request->user()->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function show(Expense $expense, ExpenseAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $expense);

        if ((int) $attachment->expense_id !== (int) $expense->id
            || (int) $attachment->organization_id !== (int) $expense->organization_id) {
            abort(404);
        }

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function destroy(Expense $expense, ExpenseAttachment $attachment): RedirectResponse
    {
        $this->authorize('attach', $expense);

        if ((int) $attachment->expense_id !== (int) $expense->id
            || (int) $attachment->organization_id !== (int) $expense->organization_id) {
            abort(404);
        }

        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }
}
