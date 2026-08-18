@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro dynamic-article-page">
    <div class="container">
        <div class="page-section__header page-section__header--row dynamic-article__header">
            <div>
                <span class="section-marker">
                    <span class="locale-copy locale-copy--ru">Тендеры</span>
                    <span class="locale-copy locale-copy--ro">Licitații</span>
                    <span class="locale-copy locale-copy--en">Tenders</span>
                </span>
                <h1>
                    <span class="locale-copy locale-copy--ru">{{ $opportunity->titleFor('ru') }}</span>
                    <span class="locale-copy locale-copy--ro">{{ $opportunity->titleFor('ro') }}</span>
                    <span class="locale-copy locale-copy--en">{{ $opportunity->titleFor('en') }}</span>
                </h1>
            </div>
            <div class="dynamic-article__header-meta">
                <span class="dynamic-article__meta-label">
                    <span class="locale-copy locale-copy--ru">Дата публикации</span>
                    <span class="locale-copy locale-copy--ro">Data publicării</span>
                    <span class="locale-copy locale-copy--en">Publication date</span>
                </span>
                <time datetime="{{ $opportunity->published_at?->toDateString() }}">{{ $opportunity->published_at?->format('d.m.Y') }}</time>
                @if ($opportunity->tag)
                    <span class="dynamic-article__tag" style="--tag-color: {{ $opportunity->tag->colorValue() }};">
                        <span class="locale-copy locale-copy--ru">{{ $opportunity->tag->labelFor('ru') }}</span>
                        <span class="locale-copy locale-copy--ro">{{ $opportunity->tag->labelFor('ro') }}</span>
                        <span class="locale-copy locale-copy--en">{{ $opportunity->tag->labelFor('en') }}</span>
                    </span>
                @endif
                @if ($opportunity->application_deadline)
                    <span class="dynamic-article__deadline">
                        <span class="locale-copy locale-copy--ru">Подать заявку до {{ $opportunity->application_deadline->format('d.m.Y') }}</span>
                        <span class="locale-copy locale-copy--ro">Depuneți cererea până la {{ $opportunity->application_deadline->format('d.m.Y') }}</span>
                        <span class="locale-copy locale-copy--en">Apply by {{ $opportunity->application_deadline->format('d.m.Y') }}</span>
                    </span>
                @endif
            </div>
        </div>

        <article class="dynamic-article">
            <div class="dynamic-article__content">
                @foreach (['ru', 'ro', 'en'] as $locale)
                    <div class="dynamic-article__locale locale-copy locale-copy--{{ $locale }}">
                        @include('partials.content-blocks', ['blocks' => $opportunity->contentFor($locale)])
                    </div>
                @endforeach
            </div>
            <a class="button button--outline dynamic-article__back" href="{{ route('stories') }}">
                <span class="locale-copy locale-copy--ru">← Назад к тендерам</span>
                <span class="locale-copy locale-copy--ro">← Înapoi la licitații</span>
                <span class="locale-copy locale-copy--en">← Back to tenders</span>
            </a>
        </article>
    </div>
</section>
@endsection
