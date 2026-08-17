@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro photo-album-page" data-image-lightbox>
    <div class="container">
        <div class="page-section__header page-section__header--row photo-album-page__header">
            <div>
                <span class="section-marker">
                    <span class="locale-copy locale-copy--ru">Фотоальбом</span>
                    <span class="locale-copy locale-copy--ro">Album foto</span>
                    <span class="locale-copy locale-copy--en">Photo album</span>
                </span>
                <h1>
                    <span class="locale-copy locale-copy--ru">{{ $album->titleFor('ru') }}</span>
                    <span class="locale-copy locale-copy--ro">{{ $album->titleFor('ro') }}</span>
                    <span class="locale-copy locale-copy--en">{{ $album->titleFor('en') }}</span>
                </h1>
            </div>
            <div class="photo-album-page__meta">
                <time datetime="{{ $album->published_at?->toDateString() }}">{{ $album->published_at?->format('d.m.Y') }}</time>
                <span>
                    <span class="locale-copy locale-copy--ru">{{ $album->photoCount() }} фото</span>
                    <span class="locale-copy locale-copy--ro">{{ $album->photoCount() }} fotografii</span>
                    <span class="locale-copy locale-copy--en">{{ $album->photoCount() }} photos</span>
                </span>
            </div>
        </div>

        @if (filled($album->excerptFor('ru')))
            <div class="photo-album-page__excerpt">
                <span class="locale-copy locale-copy--ru">{{ $album->excerptFor('ru') }}</span>
                <span class="locale-copy locale-copy--ro">{{ $album->excerptFor('ro') }}</span>
                <span class="locale-copy locale-copy--en">{{ $album->excerptFor('en') }}</span>
            </div>
        @endif

        @if ($album->photoCount() === 0)
            <div class="dynamic-empty-state">
                <p>
                    <span class="locale-copy locale-copy--ru">Фотографии появятся в этом альбоме позже.</span>
                    <span class="locale-copy locale-copy--ro">Fotografiile vor apărea în acest album mai târziu.</span>
                    <span class="locale-copy locale-copy--en">Photos will appear in this album later.</span>
                </p>
            </div>
        @else
            <div class="dynamic-article__content photo-album-page__content">
                @foreach (['ru', 'ro', 'en'] as $locale)
                    <div class="dynamic-article__locale locale-copy locale-copy--{{ $locale }}">
                        @include('partials.content-blocks', ['blocks' => $album->contentFor($locale)])
                    </div>
                @endforeach
            </div>
        @endif

        <a class="button button--outline photo-album-page__back" href="{{ route('media.photos') }}">
            <span class="locale-copy locale-copy--ru">← Назад к альбомам</span>
            <span class="locale-copy locale-copy--ro">← Înapoi la albume</span>
            <span class="locale-copy locale-copy--en">← Back to albums</span>
        </a>
    </div>
</section>
@endsection
