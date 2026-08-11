<footer class="site-footer">
    <div class="container">
        <div class="site-footer__top">
            <div class="site-footer__brand">
                <a href="{{ route('home') }}" aria-label="Green Energy Hub">
                    <picture class="footer__picture">
                        <source srcset="{{ asset('images/green-energy-hub-logo.avif') }}" type="image/avif">
                        <source srcset="{{ asset('images/green-energy-hub-logo.webp') }}" type="image/webp">
                        <img class="footer__logo" src="{{ asset('images/green-energy-hub-logo.png') }}" alt="Green Energy Hub">
                    </picture>
                </a>
                <p data-i18n="footer.description">Центр экспертизы и практических решений в сфере энергоэффективности.</p>
            </div>
            <div class="site-footer__column">
                <span class="site-footer__label" data-i18n="footer.menu">Меню</span>
                <a href="{{ route('about.project') }}" data-i18n="nav.about">О проекте</a>
                <a href="{{ route('business') }}" data-i18n="nav.business">Для бизнеса</a>
                <a href="{{ route('news') }}" data-i18n="nav.news">Новости</a>
                <a href="{{ route('stories') }}" data-i18n="nav.opportunities">Возможности</a>
                <a href="{{ route('media.photos') }}" data-i18n="nav.media">Медиа</a>
                <a href="{{ route('partners') }}" data-i18n="nav.partners">Партнёры</a>
            </div>
            <div class="site-footer__column">
                <span class="site-footer__label" data-i18n="footer.contact">Контакты</span>
                <span data-i18n="footer.address">Адрес: placeholder</span>
                <span data-i18n="footer.phone">Телефон: placeholder</span>
                <span data-i18n="footer.email">E-mail: placeholder</span>
                <a href="{{ route('contacts') }}" data-i18n="home.contact.cta">Контакты <span aria-hidden="true">→</span></a>
                <div class="footer-socials" aria-label="Social media">
                    <a href="#">Facebook</a>
                    <a href="#">Instagram</a>
                    <a href="#">YouTube</a>
                </div>
            </div>
        </div>
        <div class="site-footer__bottom">
            <span data-i18n="footer.copyright">© Green Energy Hub</span>
            <span data-i18n="footer.note">Практика для устойчивого будущего</span>
        </div>
    </div>
</footer>
