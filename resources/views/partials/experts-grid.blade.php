@php($experts = $experts ?? config('experts'))

<div class="experts-grid">
    @foreach ($experts as $expert)
        <article class="expert-card">
            <picture class="expert-card__image">
                <source srcset="{{ asset('images/experts/'.$expert['image'].'.avif') }}" type="image/avif">
                <source srcset="{{ asset('images/experts/'.$expert['image'].'.webp') }}" type="image/webp">
                <img src="{{ asset('images/experts/'.$expert['image'].'.webp') }}" alt="" loading="lazy" width="900" height="675">
            </picture>
            <div class="expert-card__body">
                <h3>
                    @foreach (['ru', 'ro', 'en'] as $locale)
                        <span class="locale-copy locale-copy--{{ $locale }}">{{ $expert['name'][$locale] }}</span>
                    @endforeach
                </h3>
                @if (($showSummary ?? false) === true)
                    <p>
                        @foreach (['ru', 'ro', 'en'] as $locale)
                            <span class="locale-copy locale-copy--{{ $locale }}">{{ $expert['summary'][$locale] }}</span>
                        @endforeach
                    </p>
                @endif
            </div>
        </article>
    @endforeach
</div>
