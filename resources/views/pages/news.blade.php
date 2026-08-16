@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro">
    <div class="container">
        <div class="page-section__header">
            <div>
                <span class="section-marker">
                    <span class="locale-copy locale-copy--ru">Лента проекта</span>
                    <span class="locale-copy locale-copy--ro">Fluxul proiectului</span>
                    <span class="locale-copy locale-copy--en">Project feed</span>
                </span>
                <h1>
                    <span class="locale-copy locale-copy--ru">Новости и обновления.</span>
                    <span class="locale-copy locale-copy--ro">Noutăți și actualizări.</span>
                    <span class="locale-copy locale-copy--en">News and updates.</span>
                </h1>
            </div>
        </div>

        @if ($news->isEmpty())
            <div class="dynamic-empty-state">
                <p>
                    <span class="locale-copy locale-copy--ru">Материалы пока не опубликованы.</span>
                    <span class="locale-copy locale-copy--ro">Materialele nu au fost încă publicate.</span>
                    <span class="locale-copy locale-copy--en">Materials have not been published yet.</span>
                </p>
            </div>
        @else
            <div class="news-feed">
                @foreach ($news as $item)
                    <a class="page-news-card" href="{{ route('news.show', $item) }}">
                        <div class="page-news-card__media">
                            @include('partials.dynamic-image', ['path' => $item->cover_image, 'seed' => $item->id, 'alt' => $item->titleFor('ru')])
                            <span class="content-card__meta">{{ $item->published_at?->format('d.m.Y') }}</span>
                        </div>
                        <div class="page-news-card__body">
                            <h3>
                                <span class="locale-copy locale-copy--ru">{{ $item->titleFor('ru') }}</span>
                                <span class="locale-copy locale-copy--ro">{{ $item->titleFor('ro') }}</span>
                                <span class="locale-copy locale-copy--en">{{ $item->titleFor('en') }}</span>
                            </h3>
                            <p>
                                <span class="locale-copy locale-copy--ru">{{ \Illuminate\Support\Str::limit($item->excerptFor('ru'), \App\Support\ContentLimits::CARD_DESCRIPTION_MAX) }}</span>
                                <span class="locale-copy locale-copy--ro">{{ \Illuminate\Support\Str::limit($item->excerptFor('ro'), \App\Support\ContentLimits::CARD_DESCRIPTION_MAX) }}</span>
                                <span class="locale-copy locale-copy--en">{{ \Illuminate\Support\Str::limit($item->excerptFor('en'), \App\Support\ContentLimits::CARD_DESCRIPTION_MAX) }}</span>
                            </p>
                            <span class="card-link">
                                <span class="locale-copy locale-copy--ru">Подробнее</span>
                                <span class="locale-copy locale-copy--ro">Detalii</span>
                                <span class="locale-copy locale-copy--en">Read more</span>
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
