<?php

namespace Tests\Feature;

use Database\Seeders\VideoSeeder;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    private const INTERNAL_PAGES = [
        '/about',
        '/about/project',
        '/about/mission',
        '/about/directions',
        '/about/audits',
        '/about/results',
        '/about/reports',
        '/about/experts',
        '/business',
        '/news',
        '/stories',
        '/media/photos',
        '/media/videos',
        '/media/catalogues',
        '/partners',
        '/contacts',
    ];

    public function test_public_pages_return_successful_responses(): void
    {
        foreach (['/', ...self::INTERNAL_PAGES] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_internal_pages_start_with_content_instead_of_a_photo_hero(): void
    {
        foreach (self::INTERNAL_PAGES as $path) {
            $this->get($path)
                ->assertSee('page-section--intro', false)
                ->assertDontSee('page-hero', false);
        }
    }

    public function test_home_and_internal_feed_cards_use_isolated_components(): void
    {
        $this->get('/')
            ->assertDontSee('class="news-card"', false)
            ->assertDontSee('class="opportunity-card"', false)
            ->assertDontSee('section--feed', false)
            ->assertDontSee('section--opportunities', false)
            ->assertSee('section--experts', false)
            ->assertSee('href="'.route('news').'"', false)
            ->assertSee('href="'.route('stories').'"', false)
            ->assertDontSee('page-news-card', false)
            ->assertDontSee('page-opportunity-card', false);

        $this->get('/news')
            ->assertSee('dynamic-empty-state', false)
            ->assertDontSee('class="news-card', false);

        $this->get('/stories')
            ->assertSee('dynamic-empty-state', false)
            ->assertDontSee('class="opportunity-card', false);
    }

    public function test_experts_are_available_on_home_and_the_about_subpage(): void
    {
        $this->get('/')
            ->assertSee('section--experts', false)
            ->assertSee('Энергоэффективность зданий, теплоснабжение и ВИЭ.')
            ->assertSee('section--experts__action', false)
            ->assertSee('href="'.route('about.experts').'"', false);

        $expertsPage = $this->get('/about/experts');

        $expertsPage
            ->assertSee('Наши эксперты', false)
            ->assertSee('experts-grid', false);

        $this->assertSame(8, substr_count($expertsPage->getContent(), 'class="expert-card"'));
    }

    public function test_media_menu_items_are_separate_pages_without_anchor_links(): void
    {
        $this->get('/media')->assertRedirect('/media/photos');
        $this->get('/media/photos')
            ->assertOk()
            ->assertSee('photo-album-feed', false)
            ->assertDontSee('Практика, которую можно увидеть.', false)
            ->assertDontSee('>Фотоальбом<', false);
        $this->get('/media/videos')
            ->assertOk()
            ->assertSee('page-section--videos', false)
            ->assertDontSee('Смотреть, как работает практика.', false);
        $this->get('/media/catalogues')
            ->assertOk()
            ->assertSee('media-grid', false)
            ->assertDontSee('Материалы для работы.', false);

        foreach (['/media/photos', '/media/videos', '/media/catalogues'] as $path) {
            $this->get($path)
                ->assertDontSee('media-switcher', false)
                ->assertDontSee('media-tabs', false);
        }

        $this->get('/media/photos')
            ->assertDontSee('href="/media#', false)
            ->assertDontSee('id="videos"', false)
            ->assertDontSee('id="catalogues"', false);
    }

    public function test_video_page_has_three_in_page_video_cards_without_public_youtube_links(): void
    {
        $this->seed(VideoSeeder::class);

        $content = $this->get('/media/videos')
            ->assertOk()
            ->getContent();

        $this->assertSame(3, substr_count($content, 'data-video-card'));
        $this->assertStringContainsString('data-video-modal', $content);
        $this->assertStringContainsString('data-video-id="x0AIDgyz6Qg"', $content);
        $this->assertStringContainsString('data-video-id="VP8GqtLYr38"', $content);
        $this->assertStringContainsString('data-video-id="mgS3xvbKI3g"', $content);
        $this->assertSame(3, substr_count($content, 'data-video-trigger'));
        $this->assertStringNotContainsString('video-card__action', $content);
        $this->assertStringNotContainsString('video-modal__header', $content);
        $this->assertStringNotContainsString('data-video-modal-title', $content);

        $mainContent = explode('<footer', $content, 2)[0];
        $this->assertStringNotContainsString('YouTube', $mainContent);
        $this->assertStringNotContainsString('youtube.com', $mainContent);
        $this->assertStringNotContainsString('youtu.be', $mainContent);
        $this->assertStringNotContainsString('href="#"', $mainContent);

        $script = file_get_contents(public_path('js/site.js'));
        $this->assertStringContainsString(
            'document.querySelectorAll("[data-video-trigger]").forEach',
            $script,
        );
    }
}
