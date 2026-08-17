@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro dynamic-article-page photo-album-feed" data-image-lightbox data-photo-albums-page>
    <div class="container">
        @if ($albums->isEmpty())
            <div class="dynamic-empty-state">
                <p>
                    <span class="locale-copy locale-copy--ru">Фотоальбомы пока не опубликованы.</span>
                    <span class="locale-copy locale-copy--ro">Albumele foto nu au fost încă publicate.</span>
                    <span class="locale-copy locale-copy--en">Photo albums have not been published yet.</span>
                </p>
            </div>
        @else
            <div class="photo-album-feed__list" data-photo-album-list>
                @include('partials.photo-albums')
            </div>
            @if ($nextUrl ?? null)
                <button class="button button--outline photo-album-feed__load-more" type="button" data-photo-albums-load-more data-next-url="{{ $nextUrl }}">
                    <span class="locale-copy locale-copy--ru">Показать ещё</span>
                    <span class="locale-copy locale-copy--ro">Arată mai mult</span>
                    <span class="locale-copy locale-copy--en">Show more</span>
                </button>
            @endif
        @endif
    </div>
</section>
@endsection
