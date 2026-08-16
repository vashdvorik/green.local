<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\NewsResource;
use App\Filament\Resources\OpportunityResource;
use App\Models\News;
use App\Models\Opportunity;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Содержание сайта';

    protected ?string $description = 'Короткая сводка по материалам, доступным посетителям и ожидающим работы.';

    protected function getStats(): array
    {
        $publishedNews = News::query()->where('status', 'published')->count();
        $publishedOpportunities = Opportunity::query()->where('status', 'published')->count();
        $scheduled = News::query()->where('status', 'scheduled')->count()
            + Opportunity::query()->where('status', 'scheduled')->count();
        $drafts = News::query()->where('status', 'draft')->count()
            + Opportunity::query()->where('status', 'draft')->count();

        return [
            Stat::make('Опубликованные новости', $publishedNews)
                ->description($publishedNews === 1 ? 'материал доступен на сайте' : 'материалов доступно на сайте')
                ->color('primary')
                ->url(NewsResource::getUrl('index')),
            Stat::make('Актуальные возможности', $publishedOpportunities)
                ->description($publishedOpportunities === 1 ? 'предложение опубликовано' : 'предложений опубликовано')
                ->color('primary')
                ->url(OpportunityResource::getUrl('index')),
            Stat::make('Запланировано', $scheduled)
                ->description($scheduled === 1 ? 'материал ждёт даты публикации' : 'материалов ждут даты публикации')
                ->color('warning'),
            Stat::make('Черновики', $drafts)
                ->description($drafts === 0 ? 'все материалы разобраны' : 'можно продолжить редактирование')
                ->color($drafts === 0 ? 'success' : 'gray'),
        ];
    }
}
