<x-filament-widgets::widget class="hub-dashboard-operations">
    <section aria-labelledby="site-operations-title">
        <div class="hub-dashboard-operations__actions" aria-label="Быстрые действия">
            <a href="{{ $createNewsUrl }}" class="hub-dashboard-operations__action">
                <span aria-hidden="true">＋</span> Добавить новость
            </a>
            <a href="{{ $createOpportunityUrl }}" class="hub-dashboard-operations__action">
                <span aria-hidden="true">＋</span> Добавить тендер
            </a>
        </div>

        <div class="hub-dashboard-operations__ai {{ $aiIsConfigured ? 'is-ready' : '' }}">
            <div>
                <strong>{{ $aiIsConfigured ? 'ИИ-перевод подключён' : 'Подключите ИИ-перевод' }}</strong>
                <p>{{ $aiIsConfigured ? 'OpenRouter готов переводить материалы на румынский и английский.' : 'Нужен ключ и модель OpenRouter — материалы пока не переводятся.' }}</p>
            </div>
            <a href="{{ $aiSettingsUrl }}">{{ $aiIsConfigured ? 'Настройки' : 'Настроить' }} <span aria-hidden="true">→</span></a>
        </div>

        <div class="hub-dashboard-operations__recent">
            <div class="hub-dashboard-operations__recent-heading">
                <h2 id="site-operations-title">Последние изменения</h2>
                <span>{{ trans_choice('{0} Нет черновиков|{1} :count черновик|[2,4] :count черновика|[5,*] :count черновиков', $draftCount) }}</span>
            </div>

            @if ($recentItems->isNotEmpty())
                <div class="hub-dashboard-operations__recent-list">
                    @foreach ($recentItems as $item)
                        <a class="hub-dashboard-operations__recent-item" href="{{ $item['url'] }}">
                            <span class="hub-dashboard-operations__recent-type">{{ $item['type'] }}</span>
                            <span class="hub-dashboard-operations__recent-title">{{ $item['title'] }}</span>
                            <time datetime="{{ $item['updated_at']->toDateString() }}">{{ $item['updated_at']->diffForHumans() }}</time>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="hub-dashboard-operations__empty">Добавьте первую новость или тендер — он появится здесь.</p>
            @endif
        </div>
    </section>
</x-filament-widgets::widget>
