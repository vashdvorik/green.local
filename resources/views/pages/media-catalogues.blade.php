@extends('layouts.site')

@section('content')
<section class="page-section page-section--intro">
    <div class="container">
        <div class="media-grid">
            <article class="media-card media-card--document">
                <span class="document-icon">PDF</span>
                <span class="content-card__meta">PDF · [дата]</span>
                <h3>
                    <span class="locale-copy locale-copy--ru">Каталог Green Energy Hub</span>
                    <span class="locale-copy locale-copy--ro">Catalogul Green Energy Hub</span>
                    <span class="locale-copy locale-copy--en">Green Energy Hub catalogue</span>
                </h3>
                <p>
                    <span class="locale-copy locale-copy--ru">Placeholder для брошюры или презентации проекта.</span>
                    <span class="locale-copy locale-copy--ro">Placeholder pentru o broșură sau prezentare a proiectului.</span>
                    <span class="locale-copy locale-copy--en">A placeholder for a project brochure or presentation.</span>
                </p>
                <a class="card-link" href="#">
                    <span class="locale-copy locale-copy--ru">Открыть / скачать PDF</span>
                    <span class="locale-copy locale-copy--ro">Deschideți / descărcați PDF</span>
                    <span class="locale-copy locale-copy--en">Open / download PDF</span> <span aria-hidden="true">↗</span>
                </a>
            </article>
        </div>
    </div>
</section>
@endsection
