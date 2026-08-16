@php
    $fallbacks = $fallbacks ?? [
        'energy-infrastructure',
        'energy-hero',
        'hero-beta',
        'infrastructure-beta',
        'project-beta',
    ];
    $fallback = $fallbacks[abs(crc32((string) ($seed ?? 0))) % count($fallbacks)];
@endphp
@if (filled($path ?? null))
    <img src="{{ asset('storage/' . ltrim($path, '/')) }}" alt="{{ $alt ?? '' }}" loading="{{ $loading ?? 'lazy' }}">
@else
    @include('partials.responsive-image', ['name' => $fallback, 'alt' => $alt ?? '', 'loading' => $loading ?? 'lazy'])
@endif
