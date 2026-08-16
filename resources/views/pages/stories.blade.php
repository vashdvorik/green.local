@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro">
    <div class="container">
        <div class="page-section__header page-section__header--row">
            <div>
                <span class="section-marker">
                    <span class="locale-copy locale-copy--ru">Лента возможностей</span>
                    <span class="locale-copy locale-copy--ro">Fluxul oportunităților</span>
                    <span class="locale-copy locale-copy--en">Opportunities feed</span>
                </span>
                <h1>
                    <span class="locale-copy locale-copy--ru">Найдите следующий шаг.</span>
                    <span class="locale-copy locale-copy--ro">Găsiți următorul pas.</span>
                    <span class="locale-copy locale-copy--en">Find your next step.</span>
                </h1>
            </div>
            <label class="filter-toggle">
                <input type="checkbox" data-opportunity-filter>
                <span>
                    <span class="locale-copy locale-copy--ru">Показывать только актуальные</span>
                    <span class="locale-copy locale-copy--ro">Afișați doar oportunitățile actuale</span>
                    <span class="locale-copy locale-copy--en">Show current only</span>
                </span>
            </label>
        </div>

        @if ($opportunities->isEmpty())
            <div class="dynamic-empty-state">
                <span class="section-marker">Green Energy Hub</span>
                <p>
                    <span class="locale-copy locale-copy--ru">Материалы пока не опубликованы.</span>
                    <span class="locale-copy locale-copy--ro">Materialele nu au fost încă publicate.</span>
                    <span class="locale-copy locale-copy--en">Materials have not been published yet.</span>
                </p>
            </div>
        @else
            <div class="opportunity-feed">
                @foreach ($opportunities as $item)
                    @php($isClosed = $item->application_deadline?->isBefore(today()) ?? false)
                    <a class="page-opportunity-card{{ $isClosed ? ' page-opportunity-card--closed' : '' }}" href="{{ route('stories.show', $item) }}" data-opportunity-card data-status="{{ $isClosed ? 'closed' : 'current' }}">
                        <div class="page-opportunity-card__media">
                            @include('partials.dynamic-image', ['path' => $item->cover_image, 'seed' => $item->id, 'alt' => $item->titleFor('ru')])
                            @if ($item->tag)
                                <span class="opportunity-tag" style="--tag-color: {{ $item->tag->colorValue() }}; color: var(--ink);">
                                    <span class="locale-copy locale-copy--ru">{{ $item->tag->labelFor('ru') }}</span>
                                    <span class="locale-copy locale-copy--ro">{{ $item->tag->labelFor('ro') }}</span>
                                    <span class="locale-copy locale-copy--en">{{ $item->tag->labelFor('en') }}</span>
                                </span>
                            @endif
                        </div>
                        <div class="page-opportunity-card__body">
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
                            <div class="page-opportunity-card__footer">
                                <span class="page-opportunity-card__deadline">
                                    @if ($isClosed)
                                        <span class="locale-copy locale-copy--ru">Приём завершён</span>
                                        <span class="locale-copy locale-copy--ro">Înscrierile s-au încheiat</span>
                                        <span class="locale-copy locale-copy--en">Applications closed</span>
                                    @elseif ($item->application_deadline)
                                        <span class="locale-copy locale-copy--ru">Подать заявку до {{ $item->application_deadline->format('d.m.Y') }}</span>
                                        <span class="locale-copy locale-copy--ro">Depuneți cererea până la {{ $item->application_deadline->format('d.m.Y') }}</span>
                                        <span class="locale-copy locale-copy--en">Apply by {{ $item->application_deadline->format('d.m.Y') }}</span>
                                    @endif
                                </span>
                                <span class="card-link">
                                    <span class="locale-copy locale-copy--ru">Подробнее</span>
                                    <span class="locale-copy locale-copy--ro">Detalii</span>
                                    <span class="locale-copy locale-copy--en">Details</span>
                                    <span aria-hidden="true">→</span>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
