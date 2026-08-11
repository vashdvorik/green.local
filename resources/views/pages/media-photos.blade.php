@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro">
    <div class="container">
        @include('partials.media-tabs', ['active' => 'photos'])

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

        <div class="media-grid">
            <article class="media-card">
                <div class="media-card__image"><img src="{{ asset('images/energy-project.png') }}" alt="" loading="lazy"></div>
                <span class="content-card__meta">[дата] · [тема]</span>
                <h3>
                    <span class="locale-copy locale-copy--ru">Энергетическая инфраструктура</span>
                    <span class="locale-copy locale-copy--ro">Infrastructură energetică</span>
                    <span class="locale-copy locale-copy--en">Energy infrastructure</span>
                </h3>
                <p>
                    <span class="locale-copy locale-copy--ru">Фотоальбом проекта и его энергетических решений.</span>
                    <span class="locale-copy locale-copy--ro">Album foto al proiectului și al soluțiilor sale energetice.</span>
                    <span class="locale-copy locale-copy--en">A photo album of the project and its energy solutions.</span>
                </p>
            </article>
            <article class="media-card">
                <div class="media-card__image"><img src="{{ asset('images/energy-infrastructure.png') }}" alt="" loading="lazy"></div>
                <span class="content-card__meta">[дата] · [тема]</span>
                <h3>
                    <span class="locale-copy locale-copy--ru">Обучение и оборудование</span>
                    <span class="locale-copy locale-copy--ro">Instruire și echipamente</span>
                    <span class="locale-copy locale-copy--en">Training and equipment</span>
                </h3>
                <p>
                    <span class="locale-copy locale-copy--ru">Место для галереи практических занятий.</span>
                    <span class="locale-copy locale-copy--ro">Loc pentru galeria sesiunilor practice.</span>
                    <span class="locale-copy locale-copy--en">A place for a gallery of practical sessions.</span>
                </p>
            </article>
        </div>
    </div>
</section>
@endsection
