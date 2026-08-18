<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    public function test_dashboard_shows_compact_editorial_workflow(): void
    {
        Config::set('admin.email', 'admin@example.test');
        SiteSetting::putValue('images.max_dimension', 1800);
        SiteSetting::putValue('images.avif_quality', 72);
        SiteSetting::putEncrypted('ai.openrouter_api_key', 'test-key');
        SiteSetting::putValue('ai.openrouter_model', 'openrouter/test-model');

        News::create([
            'slug' => 'dashboard-news',
            'status' => 'published',
            'published_at' => now(),
            'title' => ['ru' => 'Материал для панели управления'],
            'excerpt' => ['ru' => 'Краткое описание'],
            'content' => ['ru' => []],
        ]);

        $admin = User::factory()->create(['email' => 'admin@example.test']);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Инфопанель')
            ->assertSee('Добавить новость')
            ->assertSee('Добавить тендер')
            ->assertSee('ИИ-перевод подключён')
            ->assertSee('Последние изменения')
            ->assertSee('Материал для панели управления')
            ->assertDontSee('Содержание сайта')
            ->assertDontSee('AVIF · до 1 800 px');
    }
}
