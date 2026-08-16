@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro">
    <div class="container">
        <div class="page-section__header">
            <span class="section-marker">
                <span class="locale-copy locale-copy--ru">Фотоальбомы</span>
                <span class="locale-copy locale-copy--ro">Albume foto</span>
                <span class="locale-copy locale-copy--en">Photo albums</span>
            </span>
            <h1>
                <span class="locale-copy locale-copy--ru">Практика, которую можно увидеть.</span>
                <span class="locale-copy locale-copy--ro">Practica pe care o puteți vedea.</span>
                <span class="locale-copy locale-copy--en">Practice you can see.</span>
            </h1>
        </div>

        @if ($albums->isEmpty())
            <div class="dynamic-empty-state">
                <p>
                    <span class="locale-copy locale-copy--ru">Фотоальбомы пока не опубликованы.</span>
                    <span class="locale-copy locale-copy--ro">Albumele foto nu au fost încă publicate.</span>
                    <span class="locale-copy locale-copy--en">Photo albums have not been published yet.</span>
                </p>
            </div>
        @else
            <div class="photo-album-grid">
                @foreach ($albums as $album)
                    @php($cover = $album->cover_image ?: $album->photos->first()?->path)
                    <a class="photo-album-card" href="{{ route('media.photos.show', $album) }}">
                        <div class="photo-album-card__media">
                            @include('partials.dynamic-image', ['path' => $cover, 'seed' => $album->id, 'alt' => $album->titleFor('ru')])
                        </div>
                        <div class="photo-album-card__body">
                            <span class="content-card__meta">{{ $album->published_at?->format('d.m.Y') }} · {{ $album->photos_count }}</span>
                            <h2>
                                <span class="locale-copy locale-copy--ru">{{ $album->titleFor('ru') }}</span>
                                <span class="locale-copy locale-copy--ro">{{ $album->titleFor('ro') }}</span>
                                <span class="locale-copy locale-copy--en">{{ $album->titleFor('en') }}</span>
                            </h2>
                            @if (filled($album->excerptFor('ru')))
                                <p>
                                    <span class="locale-copy locale-copy--ru">{{ $album->excerptFor('ru') }}</span>
                                    <span class="locale-copy locale-copy--ro">{{ $album->excerptFor('ro') }}</span>
                                    <span class="locale-copy locale-copy--en">{{ $album->excerptFor('en') }}</span>
                                </p>
                            @endif
                            <span class="card-link">
                                <span class="locale-copy locale-copy--ru">Открыть альбом</span>
                                <span class="locale-copy locale-copy--ro">Deschide albumul</span>
                                <span class="locale-copy locale-copy--en">Open album</span>
                                <span aria-hidden="true">→</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
