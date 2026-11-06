<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(OrganizationContext $organizationContext): View
    {
        $this->authorize('viewAny', Announcement::class);

        $announcements = Announcement::query()
            ->where('organization_id', $organizationContext->ensureId())
            ->with('author')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('organization.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);

        return view('organization.announcements.create');
    }

    public function store(Request $request, OrganizationContext $organizationContext): RedirectResponse
    {
        $this->authorize('create', Announcement::class);

        $validated = $this->validated($request);

        Announcement::query()->create([
            ...$validated,
            'organization_id' => $organizationContext->ensureId(),
            'created_by' => $request->user()->id,
            'published_at' => $validated['is_published'] ? now() : null,
        ]);

        return redirect()->route('organization.announcements.index')->with('success', 'Announcement saved.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('update', $announcement);

        return view('organization.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $validated = $this->validated($request);

        $announcement->update([
            ...$validated,
            'published_at' => $validated['is_published']
                ? ($announcement->published_at ?? now())
                : null,
        ]);

        return redirect()->route('organization.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $announcement->delete();

        return redirect()->route('organization.announcements.index')->with('success', 'Announcement removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:10000'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $validated['is_published'] = $request->boolean('is_published');

        return $validated;
    }
}
