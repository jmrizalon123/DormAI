<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Ai\ListingAssistantService;
use App\Services\Property\OccupancyService;
use App\Services\Property\PublicPropertyCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceAssistantController extends Controller
{
    public function __invoke(
        Request $request,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
        ListingAssistantService $assistant,
    ): JsonResponse {
        if (! $assistant->isEnabled()) {
            return response()->json([
                'error' => 'Assistant is not available.',
            ], 503);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'messages' => ['nullable', 'array', 'max:40'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:4000'],
        ]);

        try {
            $payload = $assistant->replyMarketplace(
                $validated['message'],
                $validated['messages'] ?? [],
                $catalog,
                $occupancyService,
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
