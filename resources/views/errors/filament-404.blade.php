@push('styles')
    <link rel="stylesheet" href="{{ asset('css/filament-admin.css') }}">
@endpush

<x-filament-panels::layout.simple :has-topbar="false">
    <div class="fi-simple-page hub-admin-not-found">
        <div class="fi-simple-page-content">
            <header class="hub-admin-not-found__header">
                <x-filament-panels::logo />
                <span class="hub-admin-not-found__section">Инфопанель</span>
            </header>

            <main class="hub-admin-not-found__main" aria-labelledby="admin-not-found-title">
                <div class="hub-admin-not-found__visual" aria-hidden="true">
                    <span>404</span>
                    <i></i>
                </div>

                <p class="hub-admin-not-found__eyebrow">Страница не найдена</p>
                <h1 id="admin-not-found-title">Похоже, этот адрес больше не существует.</h1>
                <p class="hub-admin-not-found__description">
                    Проверьте адрес или воспользуйтесь навигацией ниже, чтобы продолжить работу с сайтом.
                </p>

                <div class="hub-admin-not-found__actions">
                    <x-filament::button
                        tag="a"
                        :href="\Filament\Pages\Dashboard::getUrl(isAbsolute: false)"
                        color="primary"
                    >
                        Вернуться в инфопанель
                    </x-filament::button>
                    <x-filament::button
                        tag="a"
                        :href="route('home')"
                        color="gray"
                        outlined
                    >
                        Открыть сайт
                    </x-filament::button>
                </div>

                <p class="hub-admin-not-found__hint">
                    Если ссылка была сохранена в закладках, обновите её из актуального раздела панели.
                </p>
            </main>
        </div>
    </div>
</x-filament-panels::layout.simple>
