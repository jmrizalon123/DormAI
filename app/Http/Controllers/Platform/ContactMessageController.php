<?php

namespace App\Http\Controllers\Platform;

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
        $this->authorize('viewAny', PlatformContactMessage::class);

        $messages = PlatformContactMessage::query()
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

        return view('platform.contact-messages.index', compact('messages'));
    }

    public function show(PlatformContactMessage $contactMessage): View
    {
        $this->authorize('view', $contactMessage);

        $contactMessage->markRead();
        $contactMessage->load(['replies.author', 'user', 'property:id,name', 'organization:id,name']);

        return view('platform.contact-messages.show', [
            'message' => $contactMessage,
        ]);
    }

    public function reply(
        ReplyContactMessageRequest $request,
        PlatformContactMessage $contactMessage,
        PlatformContactService $contactService,
    ): RedirectResponse {
        $this->authorize('reply', $contactMessage);

        $contactService->reply($contactMessage, $request->user(), $request->validated('body'));

        return back()->with('success', 'Reply sent to '.$contactMessage->email.'.');
    }

    public function close(PlatformContactMessage $contactMessage, PlatformContactService $contactService): RedirectResponse
    {
        $this->authorize('close', $contactMessage);

        $contactService->close($contactMessage);

        return back()->with('success', 'Conversation marked as closed.');
    }
}
