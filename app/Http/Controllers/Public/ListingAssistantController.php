<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\Ai\ListingAssistantService;
use App\Services\Property\OccupancyService;
use App\Services\Property\PublicPropertyCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingAssistantController extends Controller
{
    public function __invoke(
        Request $request,
        Property $property,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
        ListingAssistantService $assistant,
    ): JsonResponse {
        abort_unless($catalog->isPubliclyListed($property), 404);

        if (! $assistant->isEnabled()) {
            return response()->json([
                'error' => 'Listing assistant is not available.',
            ], 503);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'messages' => ['nullable', 'array', 'max:40'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:4000'],
        ]);

        $property->load([
            'organization:id,name,slug,phone,email',
        ]);

        try {
            $payload = $assistant->reply(
                $property,
                $catalog,
                $occupancyService,
                $validated['message'],
                $validated['messages'] ?? [],
            );
        } catch (\Throwable $e) {
            report($e);

            $message = $e->getMessage();

            return response()->json([
                'error' => $message !== '' ? $message : 'We could not reach the assistant right now. Please try again in a moment.',
            ], 502);
        }

        return response()->json([
            'reply' => $payload['reply'] ?? '',
            'cards' => $payload['cards'] ?? [],
        ]);
    }
}
