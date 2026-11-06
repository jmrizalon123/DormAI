<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\ReplyContactMessageRequest;
use App\Models\PlatformContactMessage;
use App\Services\Platform\PlatformContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewOrganizationInbox', PlatformContactMessage::class);

        $orgId = $request->user()->organization_id;

        $messages = PlatformContactMessage::query()
            ->where('organization_id', $orgId)
            ->with('property:id,name')
            ->withCount('replies')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('organization.contact-messages.index', compact('messages'));
    }

    public function show(Request $request, PlatformContactMessage $contactMessage): View
    {
        $this->authorize('view', $contactMessage);

        abort_unless(
            (int) $contactMessage->organization_id === (int) $request->user()->organization_id,
            404
        );

        $contactMessage->markRead();
        $contactMessage->load(['replies.author', 'user', 'property:id,name', 'organization:id,name']);

        return view('organization.contact-messages.show', [
            'message' => $contactMessage,
        ]);
    }

    public function reply(
        Request $request,
        ReplyContactMessageRequest $replyRequest,
        PlatformContactMessage $contactMessage,
        PlatformContactService $contactService,
    ): RedirectResponse {
        $this->authorize('reply', $contactMessage);

        abort_unless(
            (int) $contactMessage->organization_id === (int) $request->user()->organization_id,
            404
        );

        $contactService->reply($contactMessage, $replyRequest->user(), $replyRequest->validated('body'));

        return back()->with('success', 'Reply sent to '.$contactMessage->email.'.');
    }

    public function close(
        Request $request,
        PlatformContactMessage $contactMessage,
        PlatformContactService $contactService,
    ): RedirectResponse {
        $this->authorize('close', $contactMessage);

        abort_unless(
            (int) $contactMessage->organization_id === (int) $request->user()->organization_id,
            404
        );

        $contactService->close($contactMessage);

        return back()->with('success', 'Conversation marked as closed.');
    }
}
