<?php

namespace Tests\Feature;

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
            ->assertSee('class="news-card"', false)
            ->assertSee('class="opportunity-card"', false)
            ->assertDontSee('page-news-card', false)
            ->assertDontSee('page-opportunity-card', false);

        $this->get('/news')
            ->assertSee('page-news-card', false)
            ->assertDontSee('class="news-card', false);

        $this->get('/stories')
            ->assertSee('page-opportunity-card', false)
            ->assertDontSee('class="opportunity-card', false);
    }

    public function test_media_menu_items_are_separate_pages_without_anchor_links(): void
    {
        $this->get('/media')->assertRedirect('/media/photos');
        $this->get('/media/photos')->assertOk()->assertSee('Фотоальбомы', false);
        $this->get('/media/videos')->assertOk()->assertSee('Смотреть, как работает практика.', false);
        $this->get('/media/catalogues')->assertOk()->assertSee('Каталоги', false);

        $this->get('/media/photos')
            ->assertSee('media-switcher', false)
            ->assertDontSee('media-tabs', false)
            ->assertDontSee('page-section__header--row', false)
            ->assertDontSee('href="/media#', false)
            ->assertDontSee('id="videos"', false)
            ->assertDontSee('id="catalogues"', false);
    }
}
