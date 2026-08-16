@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro dynamic-article-page" data-image-lightbox>
    <div class="container">
        <div class="page-section__header page-section__header--row dynamic-article__header">
            <div>
                <span class="section-marker">
                    <span class="locale-copy locale-copy--ru">Новости</span>
                    <span class="locale-copy locale-copy--ro">Noutăți</span>
                    <span class="locale-copy locale-copy--en">News</span>
                </span>
                <h1>
                    <span class="locale-copy locale-copy--ru">{{ $news->titleFor('ru') }}</span>
                    <span class="locale-copy locale-copy--ro">{{ $news->titleFor('ro') }}</span>
                    <span class="locale-copy locale-copy--en">{{ $news->titleFor('en') }}</span>
                </h1>
            </div>
            <div class="dynamic-article__header-meta">
                <span class="dynamic-article__meta-label">
                    <span class="locale-copy locale-copy--ru">Дата публикации</span>
                    <span class="locale-copy locale-copy--ro">Data publicării</span>
                    <span class="locale-copy locale-copy--en">Publication date</span>
                </span>
                <time datetime="{{ $news->published_at?->toDateString() }}">{{ $news->published_at?->format('d.m.Y') }}</time>
            </div>
        </div>

        <article class="dynamic-article">
            <div class="dynamic-article__content">
                @foreach (['ru', 'ro', 'en'] as $locale)
                    <div class="dynamic-article__locale locale-copy locale-copy--{{ $locale }}">
                        @include('partials.content-blocks', ['blocks' => $news->contentFor($locale)])
                    </div>
                @endforeach
            </div>
            <a class="button button--outline dynamic-article__back" href="{{ route('news') }}">
                <span class="locale-copy locale-copy--ru">← Назад к новостям</span>
                <span class="locale-copy locale-copy--ro">← Înapoi la noutăți</span>
                <span class="locale-copy locale-copy--en">← Back to news</span>
            </a>
        </article>
    </div>
</section>
@endsection
