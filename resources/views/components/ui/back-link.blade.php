@php
    $hideBack = trim($__env->yieldContent('hide-back')) === '1';
    $overrideUrl = trim($__env->yieldContent('back-url'));
    $overrideLabel = trim($__env->yieldContent('back-label'));
    $navigation = $hideBack
        ? ['url' => null, 'label' => 'Back']
        : \App\Support\View\BackNavigation::resolve(
            $overrideUrl !== '' ? $overrideUrl : null,
            $overrideLabel !== '' ? $overrideLabel : null,
        );
@endphp

@if($navigation['url'])
    <a href="{{ $navigation['url'] }}" class="da-back-link">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>
        <span>{{ $navigation['label'] }}</span>
    </a>
@endif
