<?php

namespace App\Services\Ai;

use App\Models\Property;
use App\Services\Property\PublicPropertyCatalog;
use App\Support\Property\PropertyListingSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Optional OpenAI-compatible chat completion ("companion" layer).
 * Used when API key is set; failures return null so callers fall back to rule-based replies.
 */
class ListingCompanionAiClient
{
    public function isEnabled(): bool
    {
        if (! filter_var(config('dormai.listing_ai.enabled'), FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return filled(config('dormai.listing_ai.api_key'));
    }

    /**
     * @param  array<string, mixed>  $listing
     * @param  array<string, mixed>  $occupancy
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{reply: string}|null
     */
    public function replyForListing(
        Property $property,
        array $listing,
        array $occupancy,
        ?float $displayRate,
        string $userMessage,
        array $history,
    ): ?array {
        if (! $this->isEnabled()) {
            return null;
        }

        $context = $this->buildListingContextBlock($property, $listing, $occupancy, $displayRate);

        return $this->complete(
            systemPrompt: <<<'PROMPT'
You are a friendly, concise housing companion for students and renters browsing dorm/boarding listings in the Philippines.
You help compare options, plan next steps (budget, commute, what to ask the operator), and explain listing facts in plain language.
Rules:
- Ground every factual claim ONLY in the CONTEXT block below. If something is not in CONTEXT, say it is not on file and suggest using "Message operator" or Contact us.
- Never invent rates, addresses, bed counts, policies, or availability numbers.
- Prefer short paragraphs or bullet points. You may reply in English or Taglish if the user writes that way.
- End with one practical next step when helpful (e.g. open full listing, contact operator, try a budget search).
PROMPT,
            contextLabel: "LISTING CONTEXT",
            contextBody: $context,
            userMessage: $userMessage,
            history: $history,
        );
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{reply: string}|null
     */
    public function replyForMarketplace(
        PublicPropertyCatalog $catalog,
        string $userMessage,
        array $history,
    ): ?array {
        if (! $this->isEnabled()) {
            return null;
        }

        $count = $catalog->baseQuery()->count();
        $cities = $catalog->cities()->filter()->take(25)->values()->all();

        $context = implode("\n", [
            'Active public listings count: '.$count,
            'Sample cities with listings (not exhaustive): '.(empty($cities) ? '(none)' : implode(', ', $cities)),
            'Search tips: users can filter by keyword, city, and max monthly rate on the home page.',
        ]);

        return $this->complete(
            systemPrompt: <<<'PROMPT'
You are a friendly marketplace companion for DormAI — helping people find dorms and boarding houses in the Philippines.
You explain how search works, suggest concrete filter ideas (city, budget in PHP), and encourage users to ask the assistant for database-backed recommendations.
Rules:
- Use ONLY the CONTEXT block for counts and city examples. Do not invent listing names, prices, or availability.
- If the user wants specific listings, suggest they name a city or budget (e.g. "under 8000") so the system can query the database.
- Be concise; bullet points welcome. Taglish is OK if the user uses it.
PROMPT,
            contextLabel: 'MARKETPLACE CONTEXT',
            contextBody: $context,
            userMessage: $userMessage,
            history: $history,
        );
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{reply: string}|null
     */
    private function complete(
        string $systemPrompt,
        string $contextLabel,
        string $contextBody,
        string $userMessage,
        array $history,
    ): ?array {
        $base = rtrim((string) config('dormai.listing_ai.base_url'), '/');
        $model = (string) config('dormai.listing_ai.model');
        $timeout = (int) config('dormai.listing_ai.timeout_seconds');
        $maxTokens = (int) config('dormai.listing_ai.max_output_tokens');
        $key = (string) config('dormai.listing_ai.api_key');

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'system', 'content' => "[{$contextLabel}]\n".$contextBody],
        ];

        foreach ($this->trimHistory($history) as $turn) {
            $role = $turn['role'] ?? '';
            $content = trim((string) ($turn['content'] ?? ''));
            if ($content === '' || ! in_array($role, ['user', 'assistant'], true)) {
                continue;
            }
            $messages[] = ['role' => $role, 'content' => Str::limit($content, 4000)];
        }

        $messages[] = ['role' => 'user', 'content' => Str::limit(trim($userMessage), 4000)];

        try {
            $response = Http::withToken($key)
                ->acceptJson()
                ->asJson()
                ->timeout(max(5, $timeout))
                ->post($base.'/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'max_tokens' => max(128, $maxTokens),
                    'temperature' => (float) config('dormai.listing_ai.temperature'),
                ]);

            if (! $response->successful()) {
                Log::warning('ListingCompanionAiClient: HTTP error', [
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 500),
                ]);

                return null;
            }

            $text = data_get($response->json(), 'choices.0.message.content');
            if (! is_string($text) || trim($text) === '') {
                return null;
            }

            return ['reply' => trim($text)];
        } catch (\Throwable $e) {
            Log::warning('ListingCompanionAiClient: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return list<array{role: string, content: string}>
     */
    private function trimHistory(array $history): array
    {
        $max = max(0, (int) config('dormai.listing_ai.max_history_turns')) * 2;
        if ($max === 0) {
            return [];
        }

        $recent = array_slice($history, -$max);

        return array_values(array_filter($recent, fn ($h) => is_array($h)));
    }

    /**
     * @param  array<string, mixed>  $listing
     * @param  array<string, mixed>  $occupancy
     */
    private function buildListingContextBlock(Property $property, array $listing, array $occupancy, ?float $displayRate): string
    {
        $lines = [
            'Listing ID: '.$property->id,
            'Name: '.$property->name,
            'Operator/organization: '.($property->organization->name ?? '(unknown)'),
            'Location (city, province): '.collect([$property->city, $property->province])->filter()->join(', ') ?: '(not set)',
            'Address on file: '.$this->safeLine($this->formatAddressForContext($property)),
            'Starting monthly rate on file: '.($displayRate !== null ? '₱'.number_format($displayRate, 2) : '(not set — see full listing)'),
            'Bed spaces (total): '.($occupancy['bed_spaces'] ?? 'unknown'),
            'Beds available now (occupancy): '.($occupancy['available'] ?? 'unknown'),
            'Rooms count: '.($occupancy['rooms'] ?? 'unknown'),
            'Wi‑Fi included in rent (flag): '.(($listing['wifi_included'] ?? false) ? 'yes' : 'no'),
            'Utilities included (flag): '.(($listing['utilities_included'] ?? false) ? 'yes' : 'no'),
            'Gender policy: '.PropertyListingSettings::genderPolicyLabel($listing['gender_policy'] ?? 'mixed'),
            'Min stay (months): '.(isset($listing['min_stay_months']) && $listing['min_stay_months'] !== '' ? (string) $listing['min_stay_months'] : '(not set)'),
            'Tagline: '.$this->safeLine((string) ($listing['tagline'] ?? '')),
            'Highlights: '.$this->safeLine(implode('; ', $listing['highlights'] ?? [])),
            'Amenities summary: '.$this->safeLine($this->amenitiesLine($listing)),
            'House rules (excerpt): '.$this->safeLine(Str::limit((string) ($listing['house_rules'] ?? ''), 400)),
            'Nearby / commute notes: '.$this->safeLine(Str::limit((string) ($listing['nearby_landmarks'] ?? ''), 300)),
            'Description (excerpt): '.$this->safeLine(Str::limit(strip_tags((string) ($property->description ?? '')), 500)),
            'Operator phone on file: '.($this->contactPhone($property) ?? '(not on file)'),
            'Operator email on file: '.($this->contactEmail($property) ?? '(not on file)'),
        ];

        return implode("\n", $lines);
    }

    private function formatAddressForContext(Property $property): string
    {
        return collect([
            $property->address_line1,
            $property->address_line2,
            collect([$property->city, $property->province])->filter()->join(', '),
        ])->filter()->join(', ');
    }

    private function contactPhone(Property $property): ?string
    {
        $p = $property->phone;
        if (filled($p)) {
            return (string) $p;
        }
        $org = $property->organization;

        return filled($org?->phone) ? (string) $org->phone : null;
    }

    private function contactEmail(Property $property): ?string
    {
        $p = $property->email;
        if (filled($p)) {
            return (string) $p;
        }
        $org = $property->organization;

        return filled($org?->email) ? (string) $org->email : null;
    }

    private function safeLine(string $s): string
    {
        $t = trim($s);

        return $t === '' ? '(none)' : $t;
    }

    /**
     * @param  array<string, mixed>  $listing
     */
    private function amenitiesLine(array $listing): string
    {
        $presets = collect(config('dormai.property_amenity_presets', []))->keyBy('key');
        $labels = collect($listing['amenities'] ?? [])
            ->map(fn ($key) => data_get($presets->get($key), 'label'))
            ->filter()
            ->values();
        $custom = collect($listing['custom_amenities'] ?? [])->filter()->values();
        if ($labels->isEmpty() && $custom->isEmpty()) {
            return '(none listed)';
        }

        return $labels->merge($custom)->implode(', ');
    }
}
