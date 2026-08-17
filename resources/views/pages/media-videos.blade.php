@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro page-section--videos">
    <div class="container">
        @forelse ($videos as $video)
            @if ($loop->first)
                <div class="video-grid">
            @endif
                    <article class="video-card" data-video-card data-video-id="{{ $video->youtube_id }}">
                        <button class="video-card__preview" type="button" data-video-trigger aria-label="Открыть видеоматериал">
                            <img src="{{ $video->coverUrl() }}" alt="{{ $video->titleFor('ru') }}" loading="lazy" decoding="async">
                            <span class="video-card__play" aria-hidden="true"></span>
                        </button>
                        <div class="video-card__body">
                            <span class="content-card__meta">
                                <span class="locale-copy locale-copy--ru">{{ $video->event_date?->format('d.m.Y') }}</span>
                                <span class="locale-copy locale-copy--ro">{{ $video->event_date?->format('d.m.Y') }}</span>
                                <span class="locale-copy locale-copy--en">{{ $video->event_date?->format('d.m.Y') }}</span>
                            </span>
                            <h2 class="video-card__title">
                                <span class="locale-copy locale-copy--ru">{{ $video->titleFor('ru') }}</span>
                                <span class="locale-copy locale-copy--ro">{{ $video->titleFor('ro') }}</span>
                                <span class="locale-copy locale-copy--en">{{ $video->titleFor('en') }}</span>
                            </h2>
                            <p>
                                <span class="locale-copy locale-copy--ru">{{ $video->descriptionFor('ru') }}</span>
                                <span class="locale-copy locale-copy--ro">{{ $video->descriptionFor('ro') }}</span>
                                <span class="locale-copy locale-copy--en">{{ $video->descriptionFor('en') }}</span>
                            </p>
                        </div>
                    </article>
            @if ($loop->last)
                </div>
            @endif
        @empty
            <div class="dynamic-empty-state video-empty-state">
                <span class="locale-copy locale-copy--ru">Видеоматериалы пока не добавлены.</span>
                <span class="locale-copy locale-copy--ro">Materialele video nu au fost încă adăugate.</span>
                <span class="locale-copy locale-copy--en">No video materials have been added yet.</span>
            </div>
        @endforelse
    </div>
</section>

<div class="video-modal" data-video-modal hidden>
    <div class="video-modal__dialog" role="dialog" aria-modal="true" aria-label="Видеоматериал">
        <button class="video-modal__close" type="button" data-video-modal-close aria-label="Закрыть видео">×</button>
        <div class="video-modal__player" data-video-player></div>
    </div>
</div>
@endsection
