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
                    <span class="locale-copy locale-copy--ru">{{ $album->photos->count() }} фото</span>
                    <span class="locale-copy locale-copy--ro">{{ $album->photos->count() }} fotografii</span>
                    <span class="locale-copy locale-copy--en">{{ $album->photos->count() }} photos</span>
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

        @if ($album->photos->isEmpty())
            <div class="dynamic-empty-state">
                <p>
                    <span class="locale-copy locale-copy--ru">Фотографии появятся в этом альбоме позже.</span>
                    <span class="locale-copy locale-copy--ro">Fotografiile vor apărea în acest album mai târziu.</span>
                    <span class="locale-copy locale-copy--en">Photos will appear in this album later.</span>
                </p>
            </div>
        @else
            <div class="photo-album-gallery">
                @foreach ($album->photos as $photo)
                    <figure>
                        <div class="photo-album-gallery__media">
                            @include('partials.dynamic-image', ['path' => $photo->path, 'seed' => $photo->id, 'alt' => ''])
                        </div>
                    </figure>
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
