<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminErrorPageTest extends TestCase
{
    public function test_unknown_admin_route_uses_a_recoverable_filament_404_page(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        $this->actingAs($admin)
            ->get('/admin/section-that-does-not-exist')
            ->assertStatus(404)
            ->assertSee('Страница не найдена')
            ->assertSee('Похоже, этот адрес больше не существует.')
            ->assertSee('Вернуться в инфопанель')
            ->assertSee('Открыть сайт')
            ->assertSee('css/filament-admin.css');
    }
}
