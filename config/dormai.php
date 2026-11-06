<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform organization
    |--------------------------------------------------------------------------
    |
    | Spatie permission "teams" require an organization_id on role pivots.
    | Platform-level roles (super-admin) are scoped to this reserved organization.
    |
    */

    'platform_organization_slug' => env('DORMAI_PLATFORM_ORGANIZATION_SLUG', 'dormai-platform'),

    'billing_defaults' => [
        'due_day' => 5,
        'invoice_prefix' => 'INV',
        'grace_days' => 3,
        'auto_generate_monthly' => true,
    ],

    'notification_defaults' => [
        'email_enabled' => true,
        'in_app_enabled' => true,
        'payment_reminder_days' => [7, 3, 1],
        'contract_expiry_days' => [30, 14, 7],
    ],

    /*
    |--------------------------------------------------------------------------
    | Property listing presets (public-facing marketing)
    |--------------------------------------------------------------------------
    */

    'map_defaults' => [
        'latitude' => 11.7753,
        'longitude' => 124.8861,
        'zoom' => 13,
        'label' => 'Catbalogan City, Samar',
    ],

    'listing_content_defaults' => [
        /** Public marketplace header: wordmark (two colors) + tagline under the logo */
        'brand_title_primary' => 'Dorm',
        'brand_title_accent' => 'AI',
        'brand_tagline' => 'Philippines dorm & boarding search',
        'hero_title' => 'The Philippines’ trusted dorm & boarding marketplace',
        'hero_lead' => 'Find verified dorms and boarding houses for rent across Luzon, Visayas, and Mindanao — with photos, rates, maps, and online bed requests.',
        'search_title' => 'Search listings',
        'search_subtitle' => 'Location, budget, and keywords',
        'listings_heading' => 'Properties for rent',
        'listings_empty_hint' => 'New listings are added as property owners go live.',
        'listings_no_match_hint' => 'No listings match your filters.',
        'footer_blurb' => 'Find dormitories and boarding houses across the Philippines. Compare photos, rates, and amenities — then request a bed online.',
        'hero_images' => [
            'images/marketplace-hero.jpg',
            'images/marketplace-hero-2.jpg',
            'images/marketplace-hero-3.jpg',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Public listing assistant
    |--------------------------------------------------------------------------
    |
    | Base behavior is local (rules + database). Optional "companion" layer uses an
    | OpenAI-compatible Chat Completions API when DORMAI_LISTING_AI_ENABLED=true and
    | DORMAI_LISTING_AI_API_KEY is set. If the API fails or is disabled, replies
    | fall back to the local assistant only.
    |
    | Set DORMAI_LISTING_ASSISTANT_ENABLED=false to hide chat widgets site-wide.
    |
    */

    'listing_assistant' => [
        'enabled' => env('DORMAI_LISTING_ASSISTANT_ENABLED'),
    ],

    'listing_ai' => [
        'enabled' => env('DORMAI_LISTING_AI_ENABLED', false),
        'api_key' => env('DORMAI_LISTING_AI_API_KEY'),
        /** OpenAI-compatible base URL (no trailing slash), e.g. https://api.openai.com/v1 */
        'base_url' => env('DORMAI_LISTING_AI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('DORMAI_LISTING_AI_MODEL', 'gpt-4o-mini'),
        'temperature' => (float) env('DORMAI_LISTING_AI_TEMPERATURE', 0.5),
        'max_output_tokens' => (int) env('DORMAI_LISTING_AI_MAX_OUTPUT_TOKENS', 600),
        'timeout_seconds' => (int) env('DORMAI_LISTING_AI_TIMEOUT', 25),
        /** Pairs of user+assistant turns kept for companion context */
        'max_history_turns' => (int) env('DORMAI_LISTING_AI_MAX_HISTORY_TURNS', 6),
    ],

    'property_amenity_presets' => [
        ['key' => 'wifi', 'label' => 'High-speed Wi‑Fi', 'icon' => 'bi-wifi'],
        ['key' => 'aircon', 'label' => 'Air conditioning', 'icon' => 'bi-snow'],
        ['key' => 'study_lounge', 'label' => 'Study lounge', 'icon' => 'bi-book'],
        ['key' => 'laundry', 'label' => 'Laundry area', 'icon' => 'bi-droplet'],
        ['key' => 'kitchen', 'label' => 'Shared kitchen', 'icon' => 'bi-cup-hot'],
        ['key' => 'parking', 'label' => 'Parking', 'icon' => 'bi-p-square'],
        ['key' => 'cctv', 'label' => '24/7 CCTV', 'icon' => 'bi-camera-video'],
        ['key' => 'security', 'label' => 'Security personnel', 'icon' => 'bi-shield-check'],
        ['key' => 'generator', 'label' => 'Backup power', 'icon' => 'bi-lightning-charge'],
        ['key' => 'water_dispenser', 'label' => 'Water dispenser', 'icon' => 'bi-moisture'],
        ['key' => 'gym', 'label' => 'Fitness area', 'icon' => 'bi-heart-pulse'],
        ['key' => 'rooftop', 'label' => 'Rooftop / lounge', 'icon' => 'bi-sun'],
        ['key' => 'near_campus', 'label' => 'Near campus', 'icon' => 'bi-mortarboard'],
        ['key' => 'near_mrt', 'label' => 'Near transit', 'icon' => 'bi-train-front'],
        ['key' => 'pet_friendly', 'label' => 'Pet friendly', 'icon' => 'bi-emoji-smile'],
        ['key' => 'curfew_flexible', 'label' => 'Flexible curfew', 'icon' => 'bi-clock-history'],
    ],

];
