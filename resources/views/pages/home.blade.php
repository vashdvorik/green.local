@extends('layouts.site')
@section('content')
<section class="hero">
    <div class="hero__photo" aria-hidden="true"></div>
    <div class="container hero__content">
        <div class="hero__topline">
            <span class="eyebrow eyebrow--light" data-i18n="home.label">Центр экспертизы и практических решений</span>
        </div>
        <div class="hero__grid">
            <div class="hero__copy-panel">
                <h1 data-i18n="home.title">Энергия, которую можно использовать лучше.</h1>
                <div class="hero__aside">
                    <p data-i18n="home.copy">Площадка для бизнеса, специалистов и организаций, где практические знания помогают рациональнее использовать энергию, снижать энергозатраты и внедрять современные решения.</p>
                    <div class="hero__actions">
                        <a class="button button--light" href="{{ route('about') }}" data-i18n="home.about">Узнать о проекте</a>
                        <a class="button button--outline-light" href="{{ route('stories') }}" data-i18n="home.opportunities">Возможности</a>
                    </div>
                </div>
            </div>
            <aside class="hero__insight-panel" aria-label="Рабочий цикл Green Energy Hub">
                <div class="hero__insight-heading">
                    <span class="eyebrow eyebrow--light" data-i18n="home.process.label">Как работает хаб</span>
                    <span class="hero__signal" aria-hidden="true"><span></span></span>
                </div>
                <div class="hero__process">
                    <div class="hero__process-line" aria-hidden="true"></div>
                    <div class="hero__process-step">
                        <span class="hero__process-dot" aria-hidden="true"></span>
                        <div><strong data-i18n="home.process.measure">Измерить</strong><small data-i18n="home.process.measure.copy">энергопотребление</small></div>
                    </div>
                    <div class="hero__process-step">
                        <span class="hero__process-dot" aria-hidden="true"></span>
                        <div><strong data-i18n="home.process.understand">Понять</strong><small data-i18n="home.process.understand.copy">где возникают потери</small></div>
                    </div>
                    <div class="hero__process-step">
                        <span class="hero__process-dot" aria-hidden="true"></span>
                        <div><strong data-i18n="home.process.learn">Обучить</strong><small data-i18n="home.process.learn.copy">специалистов на практике</small></div>
                    </div>
                    <div class="hero__process-step">
                        <span class="hero__process-dot" aria-hidden="true"></span>
                        <div><strong data-i18n="home.process.act">Внедрить</strong><small data-i18n="home.process.act.copy">обоснованные решения</small></div>
                    </div>
                </div>
                <p class="hero__insight-note" data-i18n="home.process.note">От данных — к практическим действиям.</p>
            </aside>
        </div>
        <div class="hero__direction-strip">
            <span class="eyebrow eyebrow--light" data-i18n="home.focus.label">Фокус хаба</span>
            <div class="hero__direction-items">
                <span class="hero__direction-item"><i aria-hidden="true"></i><span data-i18n="home.focus.efficiency">Энергоэффективность</span></span>
                <span class="hero__direction-item"><i aria-hidden="true"></i><span data-i18n="home.focus.audit">Энергетические обследования</span></span>
                <span class="hero__direction-item"><i aria-hidden="true"></i><span data-i18n="home.focus.training">Практическое обучение</span></span>
            </div>
        </div>
    </div>
</section>

<section class="section section--project">
    <div class="container project-grid">
        <div class="project-mission">
            <div class="section-marker"><span data-i18n="home.project.label">О проекте</span></div>
           
            <h2 data-i18n="home.project.title">Сделать знания и решения в сфере энергоэффективности доступными.</h2>
            <p class="body-copy" data-i18n="home.project.copy">Green Energy Hub помогает бизнесу и специалистам переходить от общего понимания энергопотребления к практическим действиям: анализу, обучению и внедрению решений.</p>
            <a class="button" href="{{ route('business') }}" data-i18n="home.project.cta">Для бизнеса <span aria-hidden="true">→</span></a>
        </div>

        <article class="project-photo-card project-photo-card--hub">
            <img src="{{ asset('images/energy-hero.png') }}" alt="" loading="lazy">
            <div class="project-photo-card__overlay">
                <p class="eyebrow eyebrow--light" data-i18n="home.project.hub.label">Центр экспертизы</p>
                <h3 data-i18n="home.project.hub.title">Green Energy Hub</h3>
                <p data-i18n="home.project.hub.copy">Центр экспертизы, практического обучения и консультационной поддержки в сфере энергоэффективности и возобновляемой энергетики.</p>
                <div class="project-photo-card__tags">
                    <span data-i18n="home.project.tag.expertise">Экспертиза</span>
                    <span data-i18n="home.project.tag.practice">Практика</span>
                    <span data-i18n="home.project.tag.support">Поддержка</span>
                </div>
            </div>
        </article>

        <article class="project-photo-card project-photo-card--practice">
            <img src="{{ asset('images/infrastructure-beta.png') }}" alt="" loading="lazy">
            <div class="project-photo-card__overlay">
                <p class="eyebrow eyebrow--light" data-i18n="home.project.practice.label">Практический подход</p>
                <h3 data-i18n="home.project.practice.title">Решения начинаются с понимания.</h3>
                <p data-i18n="home.project.practice.copy">Измерения, оборудование и знания помогают находить потери и действовать точнее.</p>
            </div>
        </article>

        <div class="project-directions">
            <div class="project-directions__heading">
                <span class="section-marker"><span data-i18n="home.project.directions.label">Направления работы</span></span>
                <span class="project-directions__signal" aria-hidden="true"></span>
            </div>
            <div class="project-direction-grid">
                <article class="project-direction-card"><span class="project-direction-card__mark">↗</span><h3 data-i18n="home.project.direction.consult.title">Консультационная поддержка</h3><p data-i18n="home.project.direction.consult.copy">Помощь по вопросам энергопотребления, затрат и современных решений.</p></article>
                <article class="project-direction-card"><span class="project-direction-card__mark">⌁</span><h3 data-i18n="home.project.direction.audit.title">Энергетические обследования</h3><p data-i18n="home.project.direction.audit.copy">Анализируем потребление, находим потери и готовим рекомендации.</p></article>
                <article class="project-direction-card"><span class="project-direction-card__mark">＋</span><h3 data-i18n="home.project.direction.training.title">Обучение специалистов</h3><p data-i18n="home.project.direction.training.copy">Соединяем теорию, практику и работу с реальными объектами.</p></article>
                <article class="project-direction-card"><span class="project-direction-card__mark">◌</span><h3 data-i18n="home.project.direction.equipment.title">Работа с оборудованием</h3><p data-i18n="home.project.direction.equipment.copy">Знакомим с измерительным, демонстрационным и учебным оборудованием.</p></article>
            </div>
        </div>
    </div>
</section>

<section class="section section--solutions">
    <div class="container">
        <div class="section-head">
            <div>
                <div class="section-marker"><span data-i18n="home.work.label">Направления работы</span></div>
                <h2 data-i18n="home.work.title">Понимать систему. Находить потери. Действовать точнее.</h2>
            </div>
            <p class="section-head__copy" data-i18n="home.work.copy">От обследования и обучения до оборудования, материалов и экспертной поддержки.</p>
        </div>
        <div class="solution-grid">
            <article class="solution-card solution-card--photo solution-card--audit">
                <div class="solution-card__media"></div>
                <div class="solution-card__body"><h3 data-i18n="home.work.audit">Энергетические обследования</h3><p data-i18n="home.work.audit.copy">Выявляем источники потребления, потери и точки для улучшения.</p><a class="card-link" href="{{ route('about') }}" data-i18n="home.work.audit.cta">Энергоаудиты <span aria-hidden="true">→</span></a></div>
            </article>
            <article class="solution-card solution-card--lime">
                <div class="solution-card__body"><h3 data-i18n="home.work.training">Обучение специалистов</h3><p data-i18n="home.work.training.copy">Теория, практика и работа с реальными объектами.</p><span class="solution-card__arrow" aria-hidden="true">↗</span></div>
            </article>
            <article class="solution-card solution-card--photo solution-card--equipment">
                <div class="solution-card__media"></div>
                <div class="solution-card__body"><h3 data-i18n="home.work.equipment">Работа с оборудованием</h3><p data-i18n="home.work.equipment.copy">Измерительное, демонстрационное и учебное оборудование в практике.</p></div>
            </article>
            <article class="solution-card solution-card--white">
                <div class="solution-card__body"><h3 data-i18n="home.work.support">Экспертная поддержка</h3><p data-i18n="home.work.support.copy">Помогаем принимать решения по энергопотреблению и энергоэффективности.</p><a class="card-link" href="{{ route('about') }}" data-i18n="home.work.cta">Все направления <span aria-hidden="true">→</span></a></div>
            </article>
        </div>
    </div>
</section>

<section class="section section--feed">
    <div class="container">
        <div class="section-head section-head--feed"><div><div class="section-marker"><span data-i18n="home.news.label">Последние новости</span></div><h2 data-i18n="home.news.title">Что происходит в хабе.</h2></div><a class="text-link" href="{{ route('news') }}" data-i18n="home.news.cta">Все новости <span aria-hidden="true">→</span></a></div>
        <div class="news-grid">
            <article class="news-card"><div class="news-card__image"><img src="{{ asset('images/energy-hero.png') }}" alt="" loading="lazy"><span class="placeholder-tag" data-i18n="placeholder.image">Фото-заполнитель</span></div><div class="news-card__meta"><span data-i18n="placeholder.date">Дата</span></div><h3 data-i18n="placeholder.news1">Заголовок новости проекта</h3><p data-i18n="placeholder.excerpt">Краткий анонс публикации появится здесь.</p><a class="card-link" href="{{ route('news') }}" data-i18n="home.read">Подробнее <span aria-hidden="true">→</span></a></article>
            <article class="news-card"><div class="news-card__image"><img src="{{ asset('images/infrastructure-beta.png') }}" alt="" loading="lazy"><span class="placeholder-tag" data-i18n="placeholder.image">Фото-заполнитель</span></div><div class="news-card__meta"><span data-i18n="placeholder.date">Дата</span></div><h3 data-i18n="placeholder.news2">Новая публикация Green Energy Hub</h3><p data-i18n="placeholder.excerpt">Краткий анонс публикации появится здесь.</p><a class="card-link" href="{{ route('news') }}" data-i18n="home.read">Подробнее <span aria-hidden="true">→</span></a></article>
            <article class="news-card"><div class="news-card__image"><img src="{{ asset('images/project-beta.png') }}" alt="" loading="lazy"><span class="placeholder-tag" data-i18n="placeholder.image">Фото-заполнитель</span></div><div class="news-card__meta"><span data-i18n="placeholder.date">Дата</span></div><h3 data-i18n="placeholder.news3">Практика, обучение и партнёрство</h3><p data-i18n="placeholder.excerpt">Краткий анонс публикации появится здесь.</p><a class="card-link" href="{{ route('news') }}" data-i18n="home.read">Подробнее <span aria-hidden="true">→</span></a></article>
        </div>
    </div>
</section>

<section class="section section--opportunities">
    <div class="container">
        <div class="section-head section-head--feed"><div><div class="section-marker"><span data-i18n="home.opps.label">Актуальные возможности</span></div><h2 data-i18n="home.opps.title">Следующий шаг может начаться здесь.</h2></div><a class="text-link" href="{{ route('stories') }}" data-i18n="home.opps.cta">Все возможности <span aria-hidden="true">→</span></a></div>
        <div class="opportunity-grid">
            <article class="opportunity-card"><div class="opportunity-card__image"><img src="{{ asset('images/project-beta.png') }}" alt="" loading="lazy"></div><span class="badge" data-i18n="placeholder.opportunity.type">ОБУЧЕНИЕ</span><h3 data-i18n="placeholder.opportunity1">Название возможности</h3><p data-i18n="placeholder.opportunity.copy">Краткое описание предложения или программы.</p><div class="opportunity-card__footer"><span data-i18n="placeholder.deadline">Дедлайн: дата</span><a class="button button--small" href="{{ route('stories') }}" data-i18n="home.details">Подробнее</a></div></article>
            <article class="opportunity-card"><div class="opportunity-card__image"><img src="{{ asset('images/infrastructure-beta.png') }}" alt="" loading="lazy"></div><span class="badge badge--dark" data-i18n="placeholder.opportunity.type2">КОНКУРС</span><h3 data-i18n="placeholder.opportunity2">Название возможности</h3><p data-i18n="placeholder.opportunity.copy">Краткое описание предложения или программы.</p><div class="opportunity-card__footer"><span data-i18n="placeholder.deadline">Дедлайн: дата</span><a class="button button--small" href="{{ route('stories') }}" data-i18n="home.details">Подробнее</a></div></article>
            <article class="opportunity-card"><div class="opportunity-card__image"><img src="{{ asset('images/energy-hero.png') }}" alt="" loading="lazy"></div><span class="badge badge--soft" data-i18n="placeholder.opportunity.type3">ПАРТНЁРСТВО</span><h3 data-i18n="placeholder.opportunity3">Название возможности</h3><p data-i18n="placeholder.opportunity.copy">Краткое описание предложения или программы.</p><div class="opportunity-card__footer"><span data-i18n="placeholder.deadline">Дедлайн: дата</span><a class="button button--small" href="{{ route('stories') }}" data-i18n="home.details">Подробнее</a></div></article>
        </div>
    </div>
</section>

<section class="section section--partners">
    <div class="container partners-grid">
        <div><div class="section-marker"><span data-i18n="home.partners.label">Партнёры</span></div><h2 data-i18n="home.partners.title">Сильнее вместе.</h2><p class="body-copy" data-i18n="home.partners.copy">Компактная сеть организаций, которые помогают развивать энергоэффективность и локальную экспертизу.</p><a class="button button--outline" href="{{ route('partners') }}" data-i18n="home.partners.cta">Все партнёры <span aria-hidden="true">→</span></a></div>
        <div class="partner-logos"><div class="partner-logo">PARTNER 01</div><div class="partner-logo">PARTNER 02</div><div class="partner-logo">PARTNER 03</div><div class="partner-logo">PARTNER 04</div></div>
    </div>
</section>

@endsection
