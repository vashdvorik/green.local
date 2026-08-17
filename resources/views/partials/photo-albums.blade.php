@foreach ($albums as $album)
    @if (! $loop->first || ! $albums->onFirstPage())
        <div class="hero__direction-strip photo-album-feed__divider" aria-hidden="true">
        </div>
    @endif

    <article class="photo-album-feed__album">
        <header class="photo-album-feed__header">
            <div>
                <h2>
                    <span class="locale-copy locale-copy--ru">{{ $album->titleFor('ru') }}</span>
                    <span class="locale-copy locale-copy--ro">{{ $album->titleFor('ro') }}</span>
                    <span class="locale-copy locale-copy--en">{{ $album->titleFor('en') }}</span>
                </h2>
            </div>
            @if ($album->excerptFor('ru') !== '' || $album->excerptFor('ro') !== '' || $album->excerptFor('en') !== '')
                <p class="photo-album-feed__description">
                    <span class="locale-copy locale-copy--ru">{{ $album->excerptFor('ru') }}</span>
                    <span class="locale-copy locale-copy--ro">{{ $album->excerptFor('ro') }}</span>
                    <span class="locale-copy locale-copy--en">{{ $album->excerptFor('en') }}</span>
                </p>
            @endif
        </header>

        @php($contentByLocale = ['ru' => $album->contentFor('ru'), 'ro' => $album->contentFor('ro'), 'en' => $album->contentFor('en')])
        @if ($album->photoCount() === 0)
            <div class="dynamic-empty-state photo-album-feed__empty">
                <p>
                    <span class="locale-copy locale-copy--ru">Фотографии появятся в этом альбоме позже.</span>
                    <span class="locale-copy locale-copy--ro">Fotografiile vor apărea în acest album mai târziu.</span>
                    <span class="locale-copy locale-copy--en">Photos will appear in this album later.</span>
                </p>
            </div>
        @else
            <div class="dynamic-article__content photo-album-feed__content">
                @foreach ($contentByLocale as $locale => $blocks)
                    <div class="dynamic-article__locale locale-copy locale-copy--{{ $locale }}">
                        @include('partials.content-blocks', ['blocks' => $blocks])
                    </div>
                @endforeach
            </div>
        @endif
    </article>
@endforeach

@if ($nextUrl ?? null)
    <template data-photo-albums-next-url="{{ $nextUrl }}"></template>
@endif
