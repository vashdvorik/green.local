@php($imageName = $name ?? '')
<picture class="image-picture">
    <source srcset="{{ asset('images/' . $imageName . '.avif') }}" type="image/avif">
    <source srcset="{{ asset('images/' . $imageName . '.webp') }}" type="image/webp">
    <img src="{{ asset('images/' . $imageName . '.png') }}" alt="{{ $alt ?? '' }}" loading="{{ $loading ?? 'lazy' }}">
</picture>
