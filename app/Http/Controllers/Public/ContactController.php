<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreContactMessageRequest;
use App\Models\PlatformContactMessage;
use App\Models\Property;
use App\Services\Property\PublicPropertyCatalog;
use App\Services\Platform\PlatformContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(Request $request, PublicPropertyCatalog $catalog): View
    {
        $user = auth()->user();
        $propertyId = (int) old('property_id', (int) $request->query('property_id', 0));
        $listingProperty = null;
        if ($propertyId > 0) {
            $candidate = Property::query()->with('organization:id,name')->find($propertyId);
            if ($candidate && $catalog->isPubliclyListed($candidate)) {
                $listingProperty = $candidate;
            } else {
                $propertyId = 0;
            }
        }

        return view('public.contact.create', [
            'prefill' => [
                'name' => old('name', $user?->name),
                'email' => old('email', $user?->email),
                'phone' => old('phone', $user?->phone),
                'subject' => old('subject', $request->query('subject', '')),
                'body' => old('body', $request->query('body', '')),
                'property_id' => $propertyId > 0 ? $propertyId : null,
            ],
            'listingProperty' => $listingProperty,
        ]);
    }

    public function store(StoreContactMessageRequest $request, PlatformContactService $contactService): RedirectResponse
    {
        $message = $contactService->submit($request->validated(), $request->user());

        return redirect()
            ->route('public.contact.sent', $message->reference)
            ->with('success', 'Your message was sent. Our team will get back to you by email.');
    }

    public function sent(string $reference): View|RedirectResponse
    {
        $message = PlatformContactMessage::query()->where('reference', $reference)->firstOrFail();

        return view('public.contact.sent', compact('message'));
    }

    public function trackForm(): View
    {
        return view('public.contact.track');
    }

    public function show(Request $request, string $reference): View
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $message = PlatformContactMessage::query()
            ->where('reference', $reference)
            ->where('email', $request->string('email'))
            ->with(['replies.author'])
            ->firstOrFail();

        return view('public.contact.show', compact('message'));
    }
}
