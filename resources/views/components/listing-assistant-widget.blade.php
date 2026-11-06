@props([
    'endpoint',
    'property' => null,
    'widgetId' => 'daListingAssistant',
    'title' => 'Listing assistant',
    'subtitle' => 'Answers use this listing’s details. Not a substitute for the operator.',
    'fabLabel' => 'Ask about this listing',
    'intro' => 'Hi — ask about rate, location, amenities, policies, or how to reserve a bed for this listing.',
    'placeholder' => 'e.g. What’s the rate? Where is it? Is Wi‑Fi included?',
    'ariaLabel' => 'Listing assistant panel',
    /** Pipe-separated quick replies shown as chips */
    'quickReplies' => '',
    /** Pre-rendered marketplace welcome: reply string + listing cards from server */
    'welcomeReply' => null,
    /** @var array<int, array<string, mixed>>|null */
    'welcomeCards' => null,
    /** Open panel once per browser on first home visit (marketplace only) */
    'autoOpenWelcomeOnce' => false,
])

<div
    class="da-listing-assistant"
    id="{{ $widgetId }}"
    data-da-assistant
    data-endpoint="{{ $endpoint }}"
    data-intro="{{ $intro }}"
    data-placeholder="{{ $placeholder }}"
    @if(filled($welcomeReply) && is_array($welcomeCards))
        data-welcome-payload="{{ e(json_encode(['reply' => $welcomeReply, 'cards' => $welcomeCards], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}"
    @endif
    @if($autoOpenWelcomeOnce)
        data-auto-open-welcome-once="true"
    @endif
    @if(filled($quickReplies))
        data-quick-replies="{{ e($quickReplies) }}"
    @endif
    @if($property)
        data-property-name="{{ $property->name }}"
    @endif
    role="complementary"
    aria-label="{{ $ariaLabel }}"
>
    <button
        type="button"
        class="btn da-listing-assistant__fab"
        data-da-assistant-toggle
        aria-expanded="false"
        aria-controls="{{ $widgetId }}-panel"
    >
        <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
        <span>{{ $fabLabel }}</span>
    </button>

    <div
        class="da-listing-assistant__panel shadow-lg"
        id="{{ $widgetId }}-panel"
        data-da-assistant-panel
        hidden
    >
        <div class="da-listing-assistant__head">
            <div class="da-listing-assistant__head-main">
                <div class="da-listing-assistant__head-icon" aria-hidden="true">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="da-listing-assistant__head-text">
                    <div class="da-listing-assistant__title">{{ $title }}</div>
                    <div class="da-listing-assistant__subtitle text-muted small">{{ $subtitle }}</div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-secondary p-0 da-listing-assistant__close" data-da-assistant-close aria-label="Close chat">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="da-listing-assistant__quick d-none" data-da-assistant-quick-wrap>
            <div class="da-listing-assistant__quick-label small text-muted">Suggestions</div>
            <div class="da-listing-assistant__quick-row" data-da-assistant-quick></div>
        </div>
        <div class="da-listing-assistant__messages small" data-da-assistant-messages tabindex="-1"></div>
        <form class="da-listing-assistant__form" data-da-assistant-form autocomplete="off">
            <label class="visually-hidden" for="{{ $widgetId }}-input">Your question</label>
            <textarea
                id="{{ $widgetId }}-input"
                class="form-control form-control-sm da-listing-assistant__input"
                data-da-assistant-input
                rows="2"
                maxlength="2000"
                placeholder="{{ $placeholder }}"
                required
            ></textarea>
            <div class="da-listing-assistant__hint small text-muted">Enter to send · Shift+Enter for new line</div>
            <button type="submit" class="btn btn-primary btn-sm w-100 da-listing-assistant__send" data-da-assistant-send>
                <span class="da-listing-assistant__send-label">Send</span>
                <span class="da-listing-assistant__send-loading d-none spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </button>
        </form>
    </div>
</div>
