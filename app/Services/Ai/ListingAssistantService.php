<?php

namespace App\Services\Ai;

use App\Models\Property;
use App\Services\Property\OccupancyService;
use App\Services\Property\PublicPropertyCatalog;
use App\Support\Platform\PlatformListingContentSettings;
use App\Support\Property\PropertyListingSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Listing assistant: database-backed rules plus optional OpenAI-compatible "companion" layer.
 */
class ListingAssistantService
{
    public function __construct(
        private ListingCompanionAiClient $companionAi,
    ) {}

    public function isConfigured(): bool
    {
        return true;
    }

    public function isAiCompanionEnabled(): bool
    {
        return $this->companionAi->isEnabled();
    }

    public function isEnabled(): bool
    {
        $flag = config('dormai.listing_assistant.enabled');
        if ($flag === null || $flag === '') {
            return true;
        }

        return filter_var($flag, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{reply: string, cards: list<array<string, mixed>>}
     */
    public function reply(
        Property $property,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
        string $userMessage,
        array $history = [],
    ): array {
        $property->loadMissing('organization:id,name,slug,phone,email');

        $listing = PropertyListingSettings::forProperty($property);
        $occupancy = $occupancyService->propertySummary($property);
        $displayRate = $catalog->displayRate($property);

        $result = $this->answerListingQuestion(
            $property,
            $listing,
            $occupancy,
            $displayRate,
            $userMessage,
            $catalog,
            $occupancyService,
            $history
        );

        return is_array($result)
            ? $result
            : $this->assistantResponse($result);
    }

    /**
     * @param  array<int, array<string, mixed>>  $history
     * @return array{reply: string, cards: list<array<string, mixed>>}
     */
    public function replyMarketplace(
        string $userMessage,
        array $history,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
    ): array {
        $result = $this->answerMarketplaceQuestion($userMessage, $catalog, $occupancyService, $history);

        return is_array($result)
            ? $result
            : $this->assistantResponse($result);
    }

    /**
     * Homepage welcome: short greeting + up to three listing cards (DB-ranked by availability, then rate).
     *
     * @return array{reply: string, cards: list<array<string, mixed>>}
     */
    public function marketplaceWelcome(PublicPropertyCatalog $catalog, OccupancyService $occupancyService): array
    {
        $content = PlatformListingContentSettings::defaults();
        $brand = trim((string) ($content['brand_title_primary'] ?? '').' '.trim((string) ($content['brand_title_accent'] ?? '')));
        $brand = trim($brand) !== '' ? trim($brand) : config('app.name');

        $query = $catalog->baseQuery()
            ->with(['organization:id,name,slug,phone,email'])
            ->withMin('rooms as min_room_rate', 'monthly_rate');

        $properties = $query->orderByDesc('updated_at')->limit(40)->get();

        if ($properties->isEmpty()) {
            return $this->assistantResponse(
                'Welcome to '.$brand.'! New listings appear here as operators publish them — check back soon or use Contact us if you need help.'
            );
        }

        $rows = collect();
        foreach ($properties as $property) {
            $occ = $occupancyService->propertySummary($property);
            $rate = $catalog->displayRate($property);
            $rows->push([
                'property' => $property,
                'rate' => $rate,
                'available' => (int) $occ['available'],
            ]);
        }

        $rows = $rows->sortBy([
            fn (array $a, array $b) => $b['available'] <=> $a['available'],
            function (array $a, array $b) {
                $ra = $a['rate'];
                $rb = $b['rate'];
                $fa = $ra === null ? PHP_FLOAT_MAX : (float) $ra;
                $fb = $rb === null ? PHP_FLOAT_MAX : (float) $rb;

                return $fa <=> $fb;
            },
        ])->values();

        $take = $rows->take(3);
        $cards = $take->map(fn (array $row) => $this->buildListingCard($row['property'], $catalog, $occupancyService))->all();

        $lines = [
            'Welcome to '.$brand.' — your dorm & boarding search companion.',
            'Here are a few listings we’re highlighting today (ranked by open beds, then starting rate). Tap a card to view full details, or type a city or budget below.',
        ];

        return $this->assistantResponse(implode("\n\n", $lines), $cards);
    }

    private function plainText(string $message): string
    {
        $s = str_replace('**', '', $message);
        $s = preg_replace('/\[(https?:\/\/[^\]]+)\]\((https?:\/\/[^)]+)\)/', '$2', $s) ?? $s;

        return $s;
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @return array{reply: string, cards: list<array<string, mixed>>}
     */
    private function assistantResponse(string $text, array $cards = []): array
    {
        return [
            'reply' => $this->plainText($text),
            'cards' => $cards,
        ];
    }

    private function platformInquiryUrl(Property $property): string
    {
        $subject = 'Inquiry: '.$property->name;
        $body = "Hello,\n\nI'm interested in \"".$property->name.'" (listing #'.$property->id.").\n\nPlease help me reach the operator or share next steps.\n\nThank you.";

        return route('public.contact.create', [
            'subject' => $subject,
            'body' => $body,
            'property_id' => $property->id,
        ]);
    }

    /**
     * Card payload for the assistant widget (structured UI).
     *
     * @return array<string, mixed>
     */
    private function buildListingCard(
        Property $property,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
    ): array {
        $property->loadMissing('organization:id,name,slug,phone,email');
        $listing = PropertyListingSettings::forProperty($property);
        $occ = $occupancyService->propertySummary($property);
        $rate = $catalog->displayRate($property);
        $org = $property->organization;

        $ownerPhone = filled($property->phone) ? $property->phone : ($org?->phone);
        $ownerEmail = filled($property->email) ? $property->email : ($org?->email);
        $ownerName = $org?->name ?? 'Operator';

        $loc = collect([$property->city, $property->province])->filter()->join(', ') ?: 'Philippines';

        $ctaPrimary = [
            'label' => 'View full listing',
            'href' => route('public.listings.show', $property),
            'variant' => 'primary',
        ];

        $operatorQuick = [];
        if (filled($ownerPhone)) {
            $tel = preg_replace('/\s+/', '', (string) $ownerPhone);
            $operatorQuick[] = [
                'key' => 'call',
                'label' => 'Call',
                'href' => 'tel:'.$tel,
            ];
        }
        if (filled($ownerEmail)) {
            $operatorQuick[] = [
                'key' => 'email',
                'label' => 'Email',
                'href' => 'mailto:'.$ownerEmail,
            ];
        }

        $contactForm = [
            'label' => 'Message operator',
            'href' => $this->platformInquiryUrl($property),
            'hint' => 'Contact us form — the operator replies by email (not live chat).',
            'variant' => 'accent',
        ];

        /** @var list<array{label: string, href: string, variant?: string, note?: string}> $actionsFlat legacy for any older clients */
        $actionsFlat = array_merge(
            [$ctaPrimary],
            array_map(
                fn (array $row) => [
                    'label' => $row['label'].' operator',
                    'href' => $row['href'],
                    'variant' => 'outline',
                ],
                $operatorQuick
            ),
            [[
                'label' => $contactForm['label'],
                'href' => $contactForm['href'],
                'variant' => 'accent',
                'note' => $contactForm['hint'],
            ]]
        );

        return [
            'kind' => 'listing',
            'property_id' => $property->id,
            'title' => $property->name,
            'location' => $loc,
            'rate_label' => $rate !== null
                ? '₱'.number_format((float) $rate, 0).'/mo starting'
                : 'See listing for rate',
            'beds_available' => (int) ($occ['available'] ?? 0),
            'wifi' => (bool) ($listing['wifi_included'] ?? false),
            'tagline' => filled($listing['tagline'] ?? null) ? Str::limit((string) $listing['tagline'], 120) : null,
            'owner' => [
                'name' => $ownerName,
                'phone' => $ownerPhone ? (string) $ownerPhone : null,
                'email' => $ownerEmail ? (string) $ownerEmail : null,
            ],
            'cta_primary' => $ctaPrimary,
            'operator_quick' => $operatorQuick,
            'contact_form' => $contactForm,
            'actions' => $actionsFlat,
        ];
    }

    /**
     * @param  array<string, mixed>  $listing
     * @param  array<string, mixed>  $occupancy
     */
    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    private function answerListingQuestion(
        Property $property,
        array $listing,
        array $occupancy,
        ?float $displayRate,
        string $userMessage,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
        array $history = [],
    ): array|string {
        $q = $this->normalizeQuestion($userMessage);

        if ($q === '' || $this->matches($q, ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'kamusta', 'kumusta'])) {
            return $this->assistantResponse('Hello! Ask about this property (price, location, beds, Wi‑Fi), or say “recommend similar” to see other listings from the database.');
        }

        if ($this->matches($q, ['help', 'what can you', 'how do you'])) {
            return $this->assistantResponse('I read live data from our database for this listing. I can also suggest similar properties or answer with stats like available beds and starting rate.');
        }

        if ($this->matches($q, ['recommend', 'similar', 'other listings', 'alternatives', 'compare', 'suggest other'])) {
            $props = $this->similarPropertyModels($property, $catalog, $occupancyService, 5);
            $cards = $props->map(fn (Property $p) => $this->buildListingCard($p, $catalog, $occupancyService))->all();

            return $this->assistantResponse(
                $props->isEmpty()
                    ? 'No other public listings matched in the database (try the home assistant to search all cities).'
                    : 'Here are similar listings from the database — use the cards to call, email, use Contact us for a listing, or open the full page.',
                $cards
            );
        }

        if ($this->matches($q, ['cheaper', 'lower price', 'more affordable', 'budget'])) {
            if ($displayRate === null) {
                return $this->assistantResponse('We don’t have a starting rate on file for this listing, so I can’t filter cheaper options automatically. Open the home assistant and ask for listings under your budget, or browse search results.');
            }

            $props = $this->cheaperAlternativeModels($property, $catalog, $occupancyService, $displayRate, 6);
            $cards = $props->map(fn (Property $p) => $this->buildListingCard($p, $catalog, $occupancyService))->all();

            return $this->assistantResponse(
                $props->isEmpty()
                    ? 'No cheaper listings found in the same area with the current data.'
                    : 'Here are lower-priced options in the database:',
                $cards
            );
        }

        if ($this->matches($q, ['price', 'rate', 'rent', 'how much', 'magkano', 'cost', 'budget', 'monthly'])) {
            if ($displayRate !== null) {
                return $this->assistantResponse('Starting monthly rate on file: **₱'.number_format($displayRate, 2).'**. Rates can vary by room or bed — use **View available beds & reserve** for line-item pricing.');
            }

            return $this->assistantResponse('No starting monthly rate is stored for this listing. Check **View available beds & reserve** or contact the operator.');
        }

        if ($this->matches($q, ['location', 'address', 'where', 'map', 'directions', 'city', 'province', 'area', ' situated'])) {
            $address = $this->formatAddress($property);
            if ($address !== '') {
                $map = $property->hasCoordinates() ? ' A map is on this listing page.' : '';

                return $this->assistantResponse('Address on file: **'.$address.'**.'.$map);
            }

            return $this->assistantResponse('No full address is stored yet. Use **Contact** or the operator’s details when listed.');
        }

        if ($this->matches($q, ['available', 'vacant', 'availability', 'units', 'beds open', 'may bakante', 'vacancy'])) {
            $avail = (int) ($occupancy['available'] ?? 0);

            return $this->assistantResponse('**'.$avail.'** bed space(s) show as available now (occupancy data). Open **View available beds & reserve** to pick a bed.');
        }

        if ($this->matches($q, ['total bed', 'how many bed', 'bed count', 'bed spaces', 'capacity'])) {
            return $this->assistantResponse('**'.$occupancy['bed_spaces'].'** total bed space(s), **'.$occupancy['available'].'** available now, **'.$occupancy['rooms'].'** room(s).');
        }

        if ($this->matches($q, ['gender', 'male', 'female', 'mixed', 'lgbt'])) {
            $label = PropertyListingSettings::genderPolicyLabel($listing['gender_policy'] ?? 'mixed');

            return $this->assistantResponse('Gender policy: **'.$label.'**.');
        }

        if ($this->matches($q, ['wifi', 'wi-fi', 'internet'])) {
            $on = (bool) ($listing['wifi_included'] ?? false);

            return $this->assistantResponse(
                $on
                    ? 'Wi‑Fi is marked as included in rent.'
                    : 'Wi‑Fi is not marked as included — confirm with the operator.'
            );
        }

        if ($this->matches($q, ['utilities', 'electric', 'electricity', 'water bill'])) {
            $on = (bool) ($listing['utilities_included'] ?? false);

            return $this->assistantResponse(
                $on
                    ? 'Utilities are marked as included in rent.'
                    : 'Utilities are marked as billed separately.'
            );
        }

        if ($this->matches($q, ['minimum stay', 'min stay', 'contract length', 'how long'])) {
            $m = $listing['min_stay_months'] ?? null;
            if (filled($m)) {
                return $this->assistantResponse('Minimum stay on file: **'.(int) $m.'** month(s).');
            }

            return $this->assistantResponse('No minimum stay is set in the listing data.');
        }

        if ($this->matches($q, ['check in', 'check-in', 'checkout', 'check out', 'curfew'])) {
            $in = PropertyListingSettings::formatListingTime($listing['check_in_time'] ?? null);
            $out = PropertyListingSettings::formatListingTime($listing['check_out_time'] ?? null);
            if ($in || $out) {
                return $this->assistantResponse('Check-in / check-out: **'.($in ?? '—').'** / **'.($out ?? '—').'**.');
            }

            return $this->assistantResponse('No check-in/out times stored.');
        }

        if ($this->matches($q, ['amenities', 'facilities', 'features'])) {
            return $this->assistantResponse($this->summarizeAmenities($listing));
        }

        if ($this->matches($q, ['rule', 'house rule', 'policy', 'pet', 'smoking', 'visitor'])) {
            $rules = trim((string) ($listing['house_rules'] ?? ''));
            if ($rules !== '') {
                return $this->assistantResponse('House rules: '.Str::limit($rules, 500));
            }

            return $this->assistantResponse('No house rules text stored.');
        }

        if ($this->matches($q, ['nearby', 'landmark', 'commute', 'school', 'campus'])) {
            $near = trim((string) ($listing['nearby_landmarks'] ?? ''));
            if ($near !== '') {
                return $this->assistantResponse('Nearby / commute: '.Str::limit($near, 500));
            }

            return $this->assistantResponse('No nearby notes stored.');
        }

        if ($this->matches($q, ['phone', 'call', 'contact', 'email', 'reach'])) {
            $parts = [];
            if (filled($property->phone)) {
                $parts[] = 'Phone: '.$property->phone;
            }
            if (filled($property->email)) {
                $parts[] = 'Email: '.$property->email;
            }
            if ($parts !== []) {
                return $this->assistantResponse(implode('. ', $parts).'.', [$this->buildListingCard($property, $catalog, $occupancyService)]);
            }

            return $this->assistantResponse('No phone or email on file for this property.', [$this->buildListingCard($property, $catalog, $occupancyService)]);
        }

        if ($this->matches($q, ['reserve', 'booking', 'book', 'apply', 'request bed'])) {
            return $this->assistantResponse('Use **View available beds & reserve** on this page to request a bed (live availability from the database).');
        }

        if ($this->matches($q, ['who manages', 'operator', 'owner', 'organization'])) {
            return $this->assistantResponse('Managed by **'.$property->organization->name.'**.');
        }

        if ($this->matches($q, ['description', 'about', 'tell me about'])) {
            $d = trim(strip_tags((string) $property->description));
            if ($d !== '') {
                return $this->assistantResponse(Str::limit($d, 600));
            }

            return $this->assistantResponse('No long description stored.');
        }

        if ($this->matches($q, ['tagline'])) {
            $t = trim((string) ($listing['tagline'] ?? ''));

            return $this->assistantResponse($t !== '' ? $t : 'No tagline set.');
        }

        $ai = $this->companionAi->replyForListing(
            $property,
            $listing,
            $occupancy,
            $displayRate,
            $userMessage,
            $history
        );
        if ($ai !== null) {
            return $this->assistantResponse(
                $ai['reply'],
                [$this->buildListingCard($property, $catalog, $occupancyService)]
            );
        }

        return $this->assistantResponse($this->listingFallbackAnswer($property, $listing, $displayRate, $userMessage));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    private function answerMarketplaceQuestion(
        string $userMessage,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
        array $history = [],
    ): array {
        $q = $this->normalizeQuestion($userMessage);
        $content = PlatformListingContentSettings::defaults();
        $cityResolved = $this->resolveCityFromMessage($userMessage, $catalog->cities());

        if ($q === '' || $this->matches($q, ['hi', 'hello', 'hey', 'kamusta', 'kumusta'])) {
            return $this->assistantResponse('Hi! I query the live listings database. Try: “show available in [city]”, “under ₱8000”, “recommend with open beds”, or “how search works”.');
        }

        if ($this->matches($q, ['help', 'what can you'])) {
            return $this->assistantResponse('You can ask for recommendations by city, max monthly budget, Wi‑Fi included, or highest availability — I’ll run a fresh query each time.');
        }

        if ($this->matches($q, ['how search', 'filter', 'keyword', 'budget', 'city dropdown', 'use search'])) {
            return $this->assistantResponse('Use Keyword, City, and Monthly budget on the home page, then Search. I can also list matches from the database if you tell me a city or budget here.');
        }

        if ($this->matches($q, ['contact', 'email platform', 'message'])) {
            return $this->assistantResponse('Platform contact: '.route('public.contact.create'));
        }

        if ($this->matches($q, ['owner', 'register', 'list my', 'landlord'])) {
            return $this->assistantResponse('List your property: '.route('register'));
        }

        if ($this->matches($q, ['how many', 'count', 'total listing', 'how many properties'])) {
            $n = $catalog->baseQuery()->count();

            return $this->assistantResponse('There are **'.$n.'** active public listing(s) in the database right now.');
        }

        $maxBudget = $this->extractMaxBudgetPeso($userMessage);
        $wantsWifi = $this->matches($q, ['wifi', 'wi-fi', 'internet']);
        $wantsAvail = $this->matches($q, ['available', 'vacant', 'open bed', 'may bakante', 'with slots']);

        if ($this->matches($q, ['recommend', 'suggest', 'best', 'top picks', 'what should i'])) {
            return $this->databaseRecommendations($catalog, $occupancyService, $cityResolved, $maxBudget, $wantsWifi, $wantsAvail);
        }

        if (
            $this->matches($q, ['show', 'list', 'find', 'search', 'properties', 'listings', 'what do you have'])
            || $cityResolved
            || $maxBudget !== null
            || $wantsWifi
            || $wantsAvail
        ) {
            return $this->databaseRecommendations($catalog, $occupancyService, $cityResolved, $maxBudget, $wantsWifi, $wantsAvail);
        }

        if ($this->matches($q, ['rate', 'price', 'cheap', 'expensive', 'magkano']) && ! $this->matches($q, ['under', 'below', 'less than'])) {
            return $this->assistantResponse('Tell me a max monthly budget (e.g. “under 10000”) or a city — I’ll list matching rows from the database. Starting rates are stored per listing; open any result for full detail.');
        }

        $ai = $this->companionAi->replyForMarketplace($catalog, $userMessage, $history);
        if ($ai !== null) {
            return $this->assistantResponse($ai['reply']);
        }

        return $this->assistantResponse('I use live listing data. Try: “recommend in '.($content['listings_heading'] ?? 'your city').' under ₱10000” or “show all with available beds”. '.Str::limit((string) ($content['hero_lead'] ?? ''), 180));
    }

    /**
     * Ranked recommendations from DB: optional city, max rate, Wi‑Fi, availability focus.
     */
    private function databaseRecommendations(
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
        ?string $cityNormalized,
        ?float $maxBudget,
        bool $requireWifi,
        bool $prioritizeAvailability,
    ): array {
        $query = $catalog->baseQuery()
            ->with(['organization:id,name,slug,phone,email'])
            ->withMin('rooms as min_room_rate', 'monthly_rate');

        if ($cityNormalized !== null) {
            $query->whereRaw('LOWER(TRIM(city)) = ?', [$cityNormalized]);
        }

        if ($maxBudget !== null) {
            $query->where(function (Builder $b) use ($maxBudget) {
                $b->where('settings->listing->starting_rate', '<=', $maxBudget)
                    ->orWhereHas('rooms', fn (Builder $room) => $room->where('monthly_rate', '<=', $maxBudget));
            });
        }

        if ($requireWifi) {
            $query->where('settings->listing->wifi_included', true);
        }

        $properties = $query->orderByDesc('updated_at')->limit(40)->get();

        if ($properties->isEmpty()) {
            return $this->assistantResponse('No listings match that query in the database. Try widening the budget, clearing the city, or turning off the Wi‑Fi filter.');
        }

        $rows = collect();
        foreach ($properties as $property) {
            $occ = $occupancyService->propertySummary($property);
            $rate = $catalog->displayRate($property);
            $rows->push([
                'property' => $property,
                'rate' => $rate,
                'available' => (int) $occ['available'],
            ]);
        }

        if ($prioritizeAvailability) {
            $rows = $rows->sortByDesc('available')->values();
        } else {
            $rows = $rows->sortBy(function (array $row) {
                $r = $row['rate'];

                return $r === null ? PHP_FLOAT_MAX : (float) $r;
            })->values();
        }

        $take = $rows->take(8);
        $sortLabel = $prioritizeAvailability ? 'available beds' : 'rate / recent updates';
        $lines = [
            'Found '.$properties->count().' match(es). Showing up to 8 cards (sorted by '.$sortLabel.').',
            'Open a card to call or email the operator, use Contact us for a listing, or open the full listing.',
        ];

        $cards = [];
        foreach ($take as $row) {
            /** @var Property $p */
            $p = $row['property'];
            $cards[] = $this->buildListingCard($p, $catalog, $occupancyService);
        }

        if ($properties->count() > 8) {
            $lines[] = 'Refine with a city or “under ₱…” in your message to narrow results.';
        }

        return $this->assistantResponse(implode("\n\n", $lines), $cards);
    }

    /**
     * @return Collection<int, Property>
     */
    private function similarPropertyModels(
        Property $current,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
        int $limit,
    ): Collection {
        $query = $catalog->baseQuery()
            ->where('id', '!=', $current->id)
            ->with(['organization:id,name,slug,phone,email'])
            ->withMin('rooms as min_room_rate', 'monthly_rate');

        if (filled($current->city)) {
            $query->where('city', $current->city);
        } elseif (filled($current->province)) {
            $query->where('province', $current->province);
        }

        $props = $query->orderByDesc('updated_at')->limit($limit * 2)->get();

        return $props->take($limit)->values();
    }

    /**
     * @return Collection<int, Property>
     */
    private function cheaperAlternativeModels(
        Property $current,
        PublicPropertyCatalog $catalog,
        OccupancyService $occupancyService,
        float $currentRate,
        int $limit,
    ): Collection {
        $query = $catalog->baseQuery()
            ->where('id', '!=', $current->id)
            ->with(['organization:id,name,slug,phone,email'])
            ->withMin('rooms as min_room_rate', 'monthly_rate')
            ->where(function (Builder $b) use ($currentRate) {
                $b->where('settings->listing->starting_rate', '<', $currentRate)
                    ->orWhereHas('rooms', fn (Builder $room) => $room->where('monthly_rate', '<', $currentRate));
            });

        if (filled($current->city)) {
            $query->where('city', $current->city);
        }

        $props = $query->orderByDesc('updated_at')->limit(25)->get();

        return $props
            ->sortBy(fn (Property $p) => $catalog->displayRate($p) ?? PHP_FLOAT_MAX)
            ->take($limit)
            ->values();
    }

    /**
     * Match user text to a city name from the public catalog (longest match first).
     */
    private function resolveCityFromMessage(string $original, Collection $cities): ?string
    {
        $hay = strtolower($original);
        $sorted = $cities->filter()->sortByDesc(fn ($c) => strlen(trim((string) $c)));

        foreach ($sorted as $city) {
            $lc = strtolower(trim((string) $city));
            if ($lc === '') {
                continue;
            }
            if (str_contains($hay, $lc)) {
                return $lc;
            }
        }

        return null;
    }

    private function extractMaxBudgetPeso(string $message): ?float
    {
        $s = strtolower($message);
        $s = str_replace([',', 'peso'], ['', ''], $s);

        if (preg_match('/(?:under|below|less than|max|budget|up to)\s*₱?\s*([0-9]+(?:\.[0-9]+)?)\s*k\b/u', $s, $m)) {
            return (float) $m[1] * 1000;
        }

        if (preg_match('/(?:under|below|less than|max|budget|up to)\s*₱?\s*([0-9]{1,3})\s*k\b/u', $s, $m)) {
            return (float) $m[1] * 1000;
        }

        if (preg_match('/₱\s*([0-9][0-9,\.]*)/u', $message, $m)) {
            $n = (float) str_replace(',', '', $m[1]);
            if ($n >= 100) {
                return $n;
            }
        }

        if (preg_match('/(?:under|below|less than|max)\s*([0-9][0-9,\.]*)\s*(?:php|pesos?)?/u', $s, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $listing
     */
    private function summarizeAmenities(array $listing): string
    {
        $presets = collect(config('dormai.property_amenity_presets', []))->keyBy('key');
        $labels = collect($listing['amenities'] ?? [])
            ->map(fn ($key) => data_get($presets->get($key), 'label'))
            ->filter()
            ->values();

        $custom = collect($listing['custom_amenities'] ?? [])->filter()->values();

        if ($labels->isEmpty() && $custom->isEmpty()) {
            return 'No amenities list stored.';
        }

        $parts = [];
        if ($labels->isNotEmpty()) {
            $parts[] = 'Amenities: '.$labels->join(', ');
        }
        if ($custom->isNotEmpty()) {
            $parts[] = 'Also: '.$custom->join(', ');
        }

        return implode('. ', $parts);
    }

    /**
     * @param  array<string, mixed>  $listing
     */
    private function listingFallbackAnswer(Property $property, array $listing, ?float $displayRate, string $userMessage): string
    {
        $haystack = strtolower(
            $property->name.' '.
            ($property->description ?? '').' '.
            ($listing['tagline'] ?? '').' '.
            ($property->city ?? '').' '.
            ($property->province ?? '').' '.
            implode(' ', $listing['highlights'] ?? []).
            implode(' ', $listing['custom_amenities'] ?? [])
        );

        $tokens = preg_split('/\s+/', $this->normalizeQuestion($userMessage)) ?: [];
        $hits = 0;
        foreach ($tokens as $token) {
            if (strlen($token) < 3) {
                continue;
            }
            if (str_contains($haystack, $token)) {
                $hits++;
            }
        }

        if ($hits >= 1) {
            return 'That may relate to **'.$property->name.'**. '.
                ($displayRate !== null ? 'Starting rate **₱'.number_format($displayRate, 2).'/mo**. ' : '').
                'Address: **'.$this->formatAddress($property).'**. Ask about price, beds, Wi‑Fi, or say “similar listings”.';
        }

        return 'I didn’t match that. Ask about price, address, beds, amenities — or “recommend similar” for other database listings.';
    }

    private function formatAddress(Property $property): string
    {
        return collect([
            $property->address_line1,
            $property->address_line2,
            collect([$property->city, $property->province])->filter()->join(', '),
            $property->postal_code,
            $property->country !== 'PH' ? $property->country : null,
        ])->filter()->join(', ');
    }

    private function normalizeQuestion(string $message): string
    {
        $s = strtolower(trim($message));
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s) ?? $s;

        return trim(preg_replace('/\s+/', ' ', $s) ?? '');
    }

    /**
     * @param  list<string>  $needles
     */
    private function matches(string $normalizedQuestion, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($normalizedQuestion, strtolower($n))) {
                return true;
            }
        }

        return false;
    }
}
