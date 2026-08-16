@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro">
    <div class="container">
        <div class="page-section__header">
            <span class="section-marker">
                <span class="locale-copy locale-copy--ru">Видеоматериалы</span>
                <span class="locale-copy locale-copy--ro">Materiale video</span>
                <span class="locale-copy locale-copy--en">Video materials</span>
            </span>
            <h1>
                <span class="locale-copy locale-copy--ru">Смотреть, как работает практика.</span>
                <span class="locale-copy locale-copy--ro">Vedeți practica în acțiune.</span>
                <span class="locale-copy locale-copy--en">See practice in action.</span>
            </h1>
        </div>

        <div class="placeholder-grid">
            <article class="content-card content-card--media">
                <div class="video-placeholder"><span aria-hidden="true">▶</span></div>
                <span class="content-card__meta">YouTube · [дата]</span>
                <h3>
                    <span class="locale-copy locale-copy--ru">Презентация Green Energy Hub</span>
                    <span class="locale-copy locale-copy--ro">Prezentarea Green Energy Hub</span>
                    <span class="locale-copy locale-copy--en">Green Energy Hub presentation</span>
                </h3>
                <p>
                    <span class="locale-copy locale-copy--ru">Placeholder для превью, названия и описания видеоматериала.</span>
                    <span class="locale-copy locale-copy--ro">Placeholder pentru previzualizare, titlu și descriere video.</span>
                    <span class="locale-copy locale-copy--en">A placeholder for a video preview, title and description.</span>
                </p>
                <a class="card-link" href="#">
                    <span class="locale-copy locale-copy--ru">Открыть видео</span>
                    <span class="locale-copy locale-copy--ro">Deschideți video</span>
                    <span class="locale-copy locale-copy--en">Open video</span> <span aria-hidden="true">↗</span>
                </a>
            </article>
        </div>
    </div>
</section>
@endsection
