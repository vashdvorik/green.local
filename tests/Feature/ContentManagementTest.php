<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\Opportunity;
use App\Models\Tag;
use App\Support\ContentLimits;
use Database\Seeders\DemoNewsSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    public function test_only_published_content_is_public_and_is_sorted_newest_first(): void
    {
        $older = News::create([
            'slug' => 'older-news',
            'status' => 'published',
            'published_at' => Carbon::parse('2026-08-01'),
            'title' => ['ru' => 'Старая новость'],
            'excerpt' => ['ru' => 'Старый анонс'],
            'content' => ['ru' => [['type' => 'paragraph', 'data' => ['text' => 'Старый текст']]]],
        ]);

        $latest = News::create([
            'slug' => 'latest-news',
            'status' => 'published',
            'published_at' => Carbon::parse('2026-08-14'),
            'title' => ['ru' => 'Свежая новость', 'en' => 'Fresh news'],
            'excerpt' => ['ru' => 'Свежий анонс'],
            'content' => ['ru' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Свежий текст']],
                ['type' => 'gallery', 'data' => ['images' => [
                    ['path' => 'content/one.avif', 'alt' => 'Первое фото'],
                    ['path' => 'content/two.avif', 'alt' => 'Второе фото'],
                    ['path' => 'content/three.avif', 'alt' => 'Третье фото'],
                ]]],
            ]],
        ]);

        News::create([
            'slug' => 'draft-news',
            'status' => 'draft',
            'title' => ['ru' => 'Черновик'],
            'excerpt' => ['ru' => 'Черновик'],
            'content' => ['ru' => []],
        ]);

        $response = $this->get('/news');

        $response->assertOk()
            ->assertSee('Свежая новость')
            ->assertSee('Старая новость')
            ->assertDontSee('Черновик')
            ->assertSee('href="'.route('news.show', $latest).'"', false)
            ->assertDontSee('<a class="card-link"', false);

        $this->assertTrue(
            News::query()->where('status', 'published')->orderByDesc('published_at')->first()->is($latest),
        );

        $this->get('/news/'.$latest->slug)
            ->assertOk()
            ->assertSee('Fresh news')
            ->assertDontSee('dynamic-article__cover')
            ->assertSee('dynamic-article__content')
            ->assertSee('dynamic-article__gallery')
            ->assertSee('dynamic-article__gallery-media')
            ->assertSee('dynamic-article__back');
        $this->get('/news/draft-news')->assertNotFound();
    }

    public function test_news_without_a_cover_uses_one_of_the_project_fallback_images(): void
    {
        $news = News::create([
            'slug' => 'fallback-cover-news',
            'status' => 'published',
            'published_at' => now(),
            'title' => ['ru' => 'Новость без обложки'],
            'excerpt' => ['ru' => 'Краткое описание'],
            'content' => ['ru' => []],
        ]);

        $response = $this->get('/news')->assertOk();
        $fallbacks = [
            'energy-infrastructure',
            'energy-hero',
            'hero-beta',
            'infrastructure-beta',
            'project-beta',
        ];

        $this->assertTrue(collect($fallbacks)->contains(
            fn (string $fallback): bool => str_contains($response->getContent(), "/images/{$fallback}.avif"),
        ));
    }

    public function test_opportunity_uses_one_tag_and_falls_back_to_russian_translation(): void
    {
        $tag = Tag::create([
            'name' => ['ru' => 'Обучение', 'ro' => 'Instruire', 'en' => 'Training'],
            'color' => '#DDF6B7',
        ]);

        $opportunity = Opportunity::create([
            'slug' => 'training',
            'status' => 'published',
            'published_at' => now(),
            'application_deadline' => now()->addDays(10),
            'tag_id' => $tag->id,
            'title' => ['ru' => 'Практическое обучение'],
            'excerpt' => ['ru' => 'Краткое описание'],
            'content' => ['ru' => [['type' => 'paragraph', 'data' => ['text' => 'Содержание']]]],
        ]);

        $this->get('/stories')
            ->assertOk()
            ->assertSee('Практическое обучение')
            ->assertSee('Обучение')
            ->assertSee('--tag-color: #DDF6B7')
            ->assertSee('href="'.route('stories.show', $opportunity).'"', false)
            ->assertDontSee('<a class="card-link"', false);

        $this->get('/stories/'.$opportunity->slug)
            ->assertOk()
            ->assertSee('Практическое обучение')
            ->assertDontSee('dynamic-article__cover')
            ->assertSee('dynamic-article__back');
    }

    public function test_card_descriptions_are_limited_to_keep_the_listing_grid_stable(): void
    {
        $longDescription = str_repeat('Практическая энергетика помогает бизнесу принимать решения. ', 8);

        News::create([
            'slug' => 'long-description-news',
            'status' => 'published',
            'published_at' => now(),
            'title' => ['ru' => 'Новость с длинным описанием'],
            'excerpt' => ['ru' => $longDescription],
            'content' => ['ru' => []],
        ]);

        Opportunity::create([
            'slug' => 'long-description-opportunity',
            'status' => 'published',
            'published_at' => now(),
            'title' => ['ru' => 'Возможность с длинным описанием'],
            'excerpt' => ['ru' => $longDescription],
            'content' => ['ru' => []],
        ]);

        $cardDescription = Str::limit($longDescription, ContentLimits::CARD_DESCRIPTION_MAX);

        $this->get('/news')
            ->assertOk()
            ->assertSee($cardDescription)
            ->assertDontSee($longDescription);

        $this->get('/stories')
            ->assertOk()
            ->assertSee($cardDescription)
            ->assertDontSee($longDescription);
    }

    public function test_demo_news_seeder_creates_three_multilingual_published_news_items_idempotently(): void
    {
        $this->seed(DemoNewsSeeder::class);
        $this->seed(DemoNewsSeeder::class);

        $this->assertSame(3, News::query()->count());
        $this->assertSame(3, News::query()->where('status', 'published')->count());
        $this->assertSame(
            'Практика измерений и работа с оборудованием',
            News::query()->where('slug', 'practical-measurements-and-equipment')->firstOrFail()->titleFor('ru'),
        );

        $this->get('/news')
            ->assertOk()
            ->assertSee('Green Energy Hub: от понимания к практическим решениям')
            ->assertSee('Практика измерений и работа с оборудованием')
            ->assertSee('Обмен опытом и развитие экспертного сообщества');
    }
}
