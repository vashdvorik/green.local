@extends('layouts.site')

@section('content')
    <section class="page-section page-section--intro experts-page">
        <div class="container">
            <div class="page-section__header page-section__header--row">
                <div>
                    <span class="section-marker">
                        <span class="locale-copy locale-copy--ru">Команда проекта</span>
                        <span class="locale-copy locale-copy--ro">Echipa proiectului</span>
                        <span class="locale-copy locale-copy--en">Project team</span>
                    </span>
                    <h1>
                        <span class="locale-copy locale-copy--ru">Наши эксперты.</span>
                        <span class="locale-copy locale-copy--ro">Experții noștri.</span>
                        <span class="locale-copy locale-copy--en">Our experts.</span>
                    </h1>
                </div>
                <p class="section-head__copy">
                    <span class="locale-copy locale-copy--ru">Специалисты Green Energy Hub помогают превращать измерения, знания и опыт в практические решения.</span>
                    <span class="locale-copy locale-copy--ro">Specialiștii Green Energy Hub transformă măsurătorile, cunoștințele și experiența în soluții practice.</span>
                    <span class="locale-copy locale-copy--en">Green Energy Hub specialists turn measurements, knowledge and experience into practical solutions.</span>
                </p>
            </div>

            @include('partials.experts-grid', ['showSummary' => true])
        </div>
    </section>
@endsection
