<header class="site-header">
    <div class="container site-header__inner">
        <a href="{{ route('home') }}" class="brand" aria-label="Green Energy Hub">
            <picture class="brand__picture">
                <source srcset="{{ asset('images/green-energy-hub-logo.avif') }}" type="image/avif">
                <source srcset="{{ asset('images/green-energy-hub-logo.webp') }}" type="image/webp">
                <img class="brand__logo" src="{{ asset('images/green-energy-hub-logo.png') }}" alt="Green Energy Hub">
            </picture>
        </a>

        <button class="menu-button" type="button" data-menu-toggle aria-expanded="false" aria-controls="primary-menu">
            <span class="sr-only" data-i18n="menu.open">Открыть меню</span>
            <span class="menu-button__icon" aria-hidden="true">☰</span>
        </button>

        <nav class="navigation" id="primary-menu" data-menu aria-label="Основная навигация">
            <div class="navigation__dropdown" data-dropdown>
                <button class="navigation__dropdown-toggle" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="true">
                    <span data-i18n="nav.about">О проекте</span>
                    <span class="navigation__chevron" aria-hidden="true"></span>
                </button>
                <div class="navigation__dropdown-menu" data-dropdown-menu>
                    <a href="{{ route('about.project') }}" data-i18n="nav.about.project">О Green Energy Hub</a>
                    <a href="{{ route('about.mission') }}" data-i18n="nav.about.mission">Миссия и цели</a>
                    <a href="{{ route('about.directions') }}" data-i18n="nav.about.directions">Направления работы</a>
                    <a href="{{ route('about.audits') }}" data-i18n="nav.about.audits">Энергоаудиты</a>
                    <a href="{{ route('about.results') }}" data-i18n="nav.about.results">Результаты проекта</a>
                    <a href="{{ route('about.reports') }}" data-i18n="nav.about.reports">Отчёты</a>
                    <a href="{{ route('about.experts') }}" data-i18n="nav.about.experts">Наши эксперты</a>
                </div>
            </div>
            <a href="{{ route('business') }}" data-i18n="nav.business">Для бизнеса</a>
            <a href="{{ route('news') }}" data-i18n="nav.news">Новости</a>
            <a href="{{ route('stories') }}" data-i18n="nav.opportunities">Возможности</a>
            <div class="navigation__dropdown" data-dropdown>
                <button class="navigation__dropdown-toggle" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="true">
                    <span data-i18n="nav.media">Медиа</span>
                    <span class="navigation__chevron" aria-hidden="true"></span>
                </button>
                <div class="navigation__dropdown-menu" data-dropdown-menu>
                    <a href="{{ route('media.photos') }}" data-i18n="nav.media.photo">Фото</a>
                    <a href="{{ route('media.videos') }}" data-i18n="nav.media.video">Видео</a>
                    <a href="{{ route('media.catalogues') }}" data-i18n="nav.media.catalogues">Каталоги</a>
                </div>
            </div>
            <a href="{{ route('partners') }}" data-i18n="nav.partners">Партнёры</a>
            <a class="navigation__contact" href="{{ route('contacts') }}" data-i18n="nav.contacts">Контакты</a>
            <div class="language" aria-label="Язык">
                <button type="button" data-language="ru" class="is-active" aria-pressed="true">RU</button>
                <button type="button" data-language="ro" aria-pressed="false">RO</button>
                <button type="button" data-language="en" aria-pressed="false">EN</button>
            </div>
        </nav>
    </div>
</header>
