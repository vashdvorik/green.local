<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\AiConnection;
use App\Filament\Resources\NewsResource;
use App\Filament\Resources\OpportunityResource;
use App\Models\News;
use App\Models\Opportunity;
use App\Models\SiteSetting;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class SiteOperations extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.site-operations';

    protected function getViewData(): array
    {
        $aiIsConfigured = filled(SiteSetting::getEncrypted('ai.openrouter_api_key'))
            && filled(SiteSetting::getValue('ai.openrouter_model'));

        return [
            'aiIsConfigured' => $aiIsConfigured,
            'aiSettingsUrl' => AiConnection::getUrl(isAbsolute: false),
            'createNewsUrl' => NewsResource::getUrl('create'),
            'createOpportunityUrl' => OpportunityResource::getUrl('create'),
            'draftCount' => News::query()->where('status', 'draft')->count()
                + Opportunity::query()->where('status', 'draft')->count(),
            'recentItems' => $this->recentItems(),
        ];
    }

    /**
     * @return Collection<int, array{type: string, title: string, status: string, updated_at: \Illuminate\Support\Carbon, url: string}>
     */
    private function recentItems(): Collection
    {
        $news = News::query()
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (News $item): array => [
                'type' => 'Новость',
                'title' => $item->titleFor('ru') ?: 'Без заголовка',
                'status' => $this->statusLabel($item->status),
                'updated_at' => $item->updated_at,
                'url' => NewsResource::getUrl('edit', ['record' => $item]),
            ]);

        $opportunities = Opportunity::query()
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Opportunity $item): array => [
                'type' => 'Возможность',
                'title' => $item->titleFor('ru') ?: 'Без заголовка',
                'status' => $this->statusLabel($item->status),
                'updated_at' => $item->updated_at,
                'url' => OpportunityResource::getUrl('edit', ['record' => $item]),
            ]);

        return $news
            ->concat($opportunities)
            ->sortByDesc('updated_at')
            ->take(5)
            ->values();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'published' => 'Опубликовано',
            'scheduled' => 'Запланировано',
            default => 'Черновик',
        };
    }
}
