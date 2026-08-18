<?php

namespace Tests\Feature;

use App\Filament\Pages\AiConnection;
use App\Filament\Pages\ImageCompression;
use App\Filament\Resources\NewsResource;
use App\Filament\Resources\NewsResource\Pages\CreateNews;
use App\Filament\Resources\NewsResource\Pages\EditNews;
use App\Filament\Resources\OpportunityResource;
use App\Filament\Resources\OpportunityResource\Pages\CreateOpportunity;
use App\Filament\Resources\OpportunityResource\Pages\EditOpportunity;
use App\Filament\Resources\PhotoAlbumResource;
use App\Filament\Resources\PhotoAlbumResource\Pages\CreatePhotoAlbum;
use App\Filament\Resources\TagResource;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Filament\Resources\VideoResource;
use App\Filament\Resources\VideoResource\Pages\CreateVideo;
use App\Filament\Resources\VideoResource\Pages\EditVideo;
use App\Models\News;
use App\Models\Opportunity;
use App\Models\PhotoAlbum;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use App\Providers\Filament\AdminPanelProvider;
use App\Services\AiTranslationService;
use App\Services\ImageProcessor;
use App\Support\ContentLimits;
use App\Support\FilamentImageUpload;
use App\Support\YouTube;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Livewire;
use Tests\TestCase;

class ContentManagementRegressionTest extends TestCase
{
    public function test_localized_models_fall_back_to_russian_without_losing_structured_content(): void
    {
        $news = News::create([
            'slug' => 'fallback-news',
            'status' => 'published',
            'published_at' => now(),
            'title' => ['ru' => 'Русский заголовок', 'en' => 'English title'],
            'excerpt' => ['ru' => 'Русское описание'],
            'content' => [
                'ru' => [['type' => 'image', 'data' => ['path' => 'content/source.avif']]],
            ],
        ]);

        $this->assertSame('English title', $news->titleFor('en'));
        $this->assertSame('Русское описание', $news->excerptFor('ro'));
        $this->assertSame('content/source.avif', data_get($news->contentFor('en'), '0.data.path'));
        $this->assertTrue($news->isPublished());
    }

    public function test_public_gallery_variants_use_the_required_grid_classes_without_photo_captions(): void
    {
        $news = News::create([
            'slug' => 'gallery-variants',
            'status' => 'published',
            'published_at' => now(),
            'title' => ['ru' => 'Галереи'],
            'excerpt' => ['ru' => 'Проверка галерей.'],
            'content' => ['ru' => [
                ['type' => 'gallery_2', 'data' => ['images' => [
                    ['path' => 'content/gallery-two-a.avif'],
                    ['path' => 'content/gallery-two-b.avif'],
                ]]],
                ['type' => 'gallery_3', 'data' => ['images' => [
                    ['path' => 'content/gallery-three-a.avif'],
                    ['path' => 'content/gallery-three-b.avif'],
                    ['path' => 'content/gallery-three-c.avif'],
                ]]],
                ['type' => 'gallery_4', 'data' => ['images' => [
                    ['path' => 'content/gallery-four-a.avif'],
                    ['path' => 'content/gallery-four-b.avif'],
                    ['path' => 'content/gallery-four-c.avif'],
                    ['path' => 'content/gallery-four-d.avif'],
                ]]],
            ]],
        ]);

        $this->get(route('news.show', $news))
            ->assertOk()
            ->assertSee('data-image-lightbox', false)
            ->assertSee('dynamic-article__gallery--2', false)
            ->assertSee('dynamic-article__gallery--3', false)
            ->assertSee('dynamic-article__gallery--4', false)
            ->assertDontSee('Описание фото', false)
            ->assertDontSee('<figcaption>', false);
    }

    public function test_image_text_presets_render_in_both_orders_and_keep_their_image_path(): void
    {
        $news = News::create([
            'slug' => 'image-text-presets',
            'status' => 'published',
            'published_at' => now(),
            'title' => ['ru' => 'Изображение и текст'],
            'excerpt' => ['ru' => 'Проверка составного блока.'],
            'content' => ['ru' => [
                ['type' => 'image_text_photo_left', 'data' => [
                    'path' => 'content/photo-left.avif',
                    'text' => '<p>Текст справа</p>',
                ]],
                ['type' => 'image_text_text_left', 'data' => [
                    'path' => 'content/photo-right.avif',
                    'text' => '<p>Текст слева</p>',
                ]],
            ]],
        ]);

        $this->get(route('news.show', $news))
            ->assertOk()
            ->assertSee('dynamic-article__image-text--photo-left', false)
            ->assertSee('dynamic-article__image-text--text-left', false)
            ->assertSee('storage/content/photo-left.avif', false)
            ->assertSee('storage/content/photo-right.avif', false)
            ->assertSee('Текст справа')
            ->assertSee('Текст слева');

        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        Livewire::test(CreateNews::class)
            ->assertFormFieldExists('content.ru', function ($field): bool {
                $photoLeft = $field->getBlock('image_text_photo_left');
                $textLeft = $field->getBlock('image_text_text_left');
                $photoLeftGrid = $photoLeft?->getChildSchema()->getComponents()[0] ?? null;
                $textLeftGrid = $textLeft?->getChildSchema()->getComponents()[0] ?? null;

                if (! $photoLeftGrid instanceof Grid || ! $textLeftGrid instanceof Grid) {
                    return false;
                }

                $photoLeftComponents = $photoLeftGrid->getChildSchema()->getComponents();
                $textLeftComponents = $textLeftGrid->getChildSchema()->getComponents();

                return $photoLeftComponents[0] instanceof FileUpload
                    && $photoLeftComponents[1] instanceof RichEditor
                    && $textLeftComponents[0] instanceof RichEditor
                    && $textLeftComponents[1] instanceof FileUpload
                    && $photoLeft->getLabel() === 'Изображение+текст'
                    && $textLeft->getLabel() === 'Текст+изображение'
                    && $photoLeftComponents[1]->getToolbarButtons() === $textLeftComponents[0]->getToolbarButtons();
            });
    }

    public function test_photo_albums_show_only_published_albums_with_ordered_photos_and_russian_fallback(): void
    {
        $album = PhotoAlbum::create([
            'slug' => 'energy-practice',
            'status' => 'published',
            'published_at' => now(),
            'title' => ['ru' => 'Энергетическая практика', 'en' => 'Energy practice'],
            'excerpt' => ['ru' => 'Практические фотографии проекта.'],
        ]);
        $album->photos()->createMany([
            ['path' => 'uploads/albums/second.avif', 'position' => 2],
            ['path' => 'uploads/albums/first.avif', 'position' => 1],
        ]);
        PhotoAlbum::create([
            'slug' => 'hidden-album',
            'status' => 'draft',
            'title' => ['ru' => 'Черновой альбом'],
        ]);
        PhotoAlbum::create([
            'slug' => 'future-album',
            'status' => 'published',
            'published_at' => now()->addDay(),
            'title' => ['ru' => 'Будущий альбом'],
        ]);

        $this->get('/media/photos')
            ->assertOk()
            ->assertSee('Энергетическая практика')
            ->assertDontSee('Черновой альбом')
            ->assertDontSee('Будущий альбом')
            ->assertSee('photo-album-feed__album', false)
            ->assertSee('dynamic-article__gallery--2', false);

        $response = $this->get('/media/photos/'.$album->slug)
            ->assertOk()
            ->assertSee('dynamic-article__gallery', false)
            ->assertDontSee('media-switcher', false)
            ->assertSee('data-image-lightbox', false)
            ->assertSee('Энергетическая практика');

        $this->assertLessThan(
            strpos($response->getContent(), 'second.avif'),
            strpos($response->getContent(), 'first.avif'),
        );
        $this->get('/media/photos/hidden-album')->assertNotFound();
        $this->get('/media/photos/future-album')->assertNotFound();
        $this->assertSame('Энергетическая практика', $album->titleFor('ro'));
    }

    public function test_photo_album_feed_renders_builder_blocks_and_loads_more_albums_by_page(): void
    {
        foreach (range(1, 7) as $index) {
            PhotoAlbum::create([
                'slug' => "builder-album-{$index}",
                'status' => 'published',
                'published_at' => now()->subMinutes($index),
                'title' => ['ru' => "Альбом {$index}"],
                'excerpt' => ['ru' => 'Описание альбома.'],
                'content' => ['ru' => [[
                    'type' => 'gallery_3',
                    'data' => ['images' => [
                        ['path' => 'uploads/albums/one.avif'],
                        ['path' => 'uploads/albums/two.avif'],
                        ['path' => 'uploads/albums/three.avif'],
                    ]],
                ]]],
            ]);
        }

        $firstPage = $this->get('/media/photos')->assertOk()->getContent();

        $this->assertSame(6, substr_count($firstPage, 'photo-album-feed__album'));
        $this->assertSame(5, substr_count($firstPage, 'photo-album-feed__divider'));
        $this->assertStringContainsString('dynamic-article__gallery--3', $firstPage);
        $this->assertStringContainsString('data-photo-albums-load-more', $firstPage);

        $secondPage = $this->get('/media/photos?page=2&fragment=1')->assertOk()->getContent();

        $this->assertSame(1, substr_count($secondPage, 'photo-album-feed__album'));
        $this->assertSame(1, substr_count($secondPage, 'photo-album-feed__divider'));
        $this->assertStringNotContainsString('data-photo-albums-next-url', $secondPage);
    }

    public function test_photo_album_content_is_shared_between_all_language_tabs(): void
    {
        $sharedContent = [[
            'type' => 'gallery_2',
            'data' => ['images' => [
                ['path' => 'uploads/albums/shared-one.avif'],
                ['path' => 'uploads/albums/shared-two.avif'],
            ]],
        ]];

        $album = PhotoAlbum::create([
            'slug' => 'shared-photo-content',
            'status' => 'published',
            'published_at' => now(),
            'title' => [
                'ru' => 'Общий фотоальбом',
                'ro' => 'Album foto comun',
                'en' => 'Shared photo album',
            ],
            'excerpt' => [
                'ru' => 'Описание',
                'ro' => 'Descriere',
                'en' => 'Description',
            ],
            'photo_content' => $sharedContent,
        ]);

        $this->assertSame($sharedContent, $album->contentFor('ru'));
        $this->assertSame($sharedContent, $album->contentFor('ro'));
        $this->assertSame($sharedContent, $album->contentFor('en'));
        $this->assertSame(2, $album->photoCount());

        $this->get('/media/photos/shared-photo-content')
            ->assertOk()
            ->assertSee('shared-one.avif')
            ->assertSee('shared-two.avif');
    }

    public function test_filament_and_laravel_core_translations_are_russian(): void
    {
        $this->assertSame('ru', app()->getLocale());
        $this->assertSame('Создать', __('filament-actions::create.single.label'));
        $this->assertSame('Выйти', __('filament-panels::layout.actions.logout.label'));
        $this->assertSame('Добавить к :label', __('filament-forms::components.builder.actions.add.label'));
        $this->assertSame('Перейти к содержимому', __('filament-panels::layout.skip_to_content.label'));
        $this->assertSame('Выбор цвета', __('filament-forms::components.color_picker.panel_label'));
        $this->assertSame('Панель инструментов редактора', __('filament-forms::components.rich_editor.toolbar.label'));
        $this->assertSame('Закрыть уведомление', __('filament-notifications::notification.actions.close.label'));
        $this->assertSame('Загрузка…', __('filament-tables::table.loading'));
        $this->assertSame('Нет данных для отображения', __('filament-widgets::chart.empty.heading'));
        $this->assertSame('Страница не найдена.', __('errors.404'));

        $translationKeys = [
            'filament-panels::layout.skip_to_content.label',
            'filament-panels::layout.actions.open_database_notifications.label_with_unread_count',
            'filament-panels::layout.actions.theme_switcher.label',
            'filament-panels::layout.navigation.label',
            'filament-panels::layout.topbar.label',
            'filament-panels::auth/http/controllers/email-change-verification-controller.notifications.unavailable.title',
            'filament-forms::components.color_picker.panel_label',
            'filament-forms::components.date_time_picker.month_select.label',
            'filament-forms::components.date_time_picker.year_input.label',
            'filament-forms::components.date_time_picker.hour_input.label',
            'filament-forms::components.date_time_picker.minute_input.label',
            'filament-forms::components.date_time_picker.second_input.label',
            'filament-forms::components.file_upload.actions.download.label',
            'filament-forms::components.file_upload.actions.open.label',
            'filament-forms::components.file_upload.editor.label',
            'filament-forms::components.key_value.columns.actions.label',
            'filament-forms::components.key_value.columns.reorder.label',
            'filament-forms::components.repeater.columns.actions.label',
            'filament-forms::components.repeater.columns.reorder.label',
            'filament-forms::components.rich_editor.toolbar.label',
            'filament-forms::components.tags_input.tag_added',
            'filament-forms::components.tags_input.tag_removed',
            'filament-infolists::components.entries.icon.true',
            'filament-infolists::components.entries.icon.false',
            'filament-notifications::notification.actions.close.label',
            'filament-notifications::database.modal.unread_label',
            'filament-query-builder::query-builder.form.or_groups.group.label',
            'filament-query-builder::query-builder.max_rules_reached_tooltip',
            'filament-schemas::components.callout.statuses.danger',
            'filament-schemas::components.callout.statuses.info',
            'filament-schemas::components.callout.statuses.success',
            'filament-schemas::components.callout.statuses.warning',
            'filament-schemas::components.section.actions.collapse.label',
            'filament-schemas::components.section.actions.expand.label',
            'filament-schemas::components.wizard.header.step.statuses.completed',
            'filament-schemas::components.wizard.header.step.statuses.upcoming',
            'filament::components/badge.actions.delete.label',
            'filament::components/breadcrumbs.label',
            'filament::components/input/one-time-code.aria_label',
            'filament::components/loading-section.label',
            'filament::components/section.actions.collapse.label',
            'filament::components/tabs.label',
            'filament-tables::table.column_manager.actions.reorder.label',
            'filament-tables::table.columns.icon.boolean.true',
            'filament-tables::table.columns.icon.boolean.false',
            'filament-tables::table.actions.reorder_record.label',
            'filament-tables::table.actions.toggle_record_content.label',
            'filament-tables::table.loading',
            'filament-tables::table.result_count',
            'filament-widgets::chart.filter.label',
            'filament-widgets::chart.empty.heading',
        ];

        foreach ($translationKeys as $key) {
            $this->assertNotSame($key, __($key), "Перевод отсутствует: {$key}");
        }

        $validator = Validator::make([], ['title' => ['required']]);

        $this->assertSame(
            'Поле заголовок обязательно для заполнения.',
            $validator->errors()->first('title'),
        );
    }

    public function test_site_settings_support_json_values_and_encrypted_secrets(): void
    {
        SiteSetting::putValue('images.max_dimension', 1440);
        SiteSetting::putEncrypted('ai.openrouter_api_key', 'test-openrouter-secret');

        $this->assertSame(1440, SiteSetting::getValue('images.max_dimension'));
        $this->assertSame('test-openrouter-secret', SiteSetting::getEncrypted('ai.openrouter_api_key'));
        $this->assertNotSame(
            'test-openrouter-secret',
            (string) SiteSetting::query()->where('key', 'ai.openrouter_api_key')->value('value'),
        );
    }

    public function test_openrouter_connection_and_translation_preserve_the_editor_block_structure(): void
    {
        SiteSetting::putEncrypted('ai.openrouter_api_key', 'secret-token');
        SiteSetting::putValue('ai.openrouter_model', 'test/model');
        SiteSetting::putValue('ai.openrouter_base_url', 'https://openrouter.test/api/v1');
        SiteSetting::putValue('ai.app_name', 'Green Energy Hub Tests');

        Http::fake([
            'https://openrouter.test/api/v1/models' => Http::response(['data' => [['id' => 'test/model']]], 200),
            'https://openrouter.test/api/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'title' => 'Titlu tradus',
                            'excerpt' => 'Descriere tradusă',
                            'content' => [
                                ['type' => 'paragraph', 'data' => ['text' => 'Text tradus']],
                                ['type' => 'image', 'data' => ['path' => 'should-be-replaced.avif', 'alt' => 'Imagine']],
                                ['type' => 'heading', 'data' => ['text' => 'Titlu secundar', 'level' => 'h2']],
                                ['type' => 'gallery_4', 'data' => ['images' => [
                                    ['path' => 'should-be-replaced-a.avif', 'alt' => 'Imagine A'],
                                    ['path' => 'should-be-replaced-b.avif', 'alt' => 'Imagine B'],
                                    ['path' => 'should-be-replaced-c.avif', 'alt' => 'Imagine C'],
                                    ['path' => 'should-be-replaced-d.avif', 'alt' => 'Imagine D'],
                                ]]],
                            ],
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
            ], 200),
        ]);

        $service = app(AiTranslationService::class);
        $service->testConnection();
        $translated = $service->translateFromRussian([
            'title' => 'Русский заголовок',
            'excerpt' => 'Русское описание',
            'content' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Русский текст']],
                ['type' => 'image', 'data' => ['path' => 'content/source.avif', 'alt' => 'Источник']],
                ['type' => 'heading', 'data' => ['text' => 'Подзаголовок', 'level' => 'h2']],
                ['type' => 'gallery_4', 'data' => ['images' => [
                    ['path' => 'content/gallery-a.avif', 'alt' => 'Галерея A'],
                    ['path' => 'content/gallery-b.avif', 'alt' => 'Галерея B'],
                    ['path' => 'content/gallery-c.avif', 'alt' => 'Галерея C'],
                    ['path' => 'content/gallery-d.avif', 'alt' => 'Галерея D'],
                ]]],
            ],
        ], 'ro');

        $this->assertSame('Titlu tradus', $translated['title']);
        $this->assertSame('content/source.avif', data_get($translated, 'content.1.data.path'));
        $this->assertSame('image', data_get($translated, 'content.1.type'));
        $this->assertCount(4, $translated['content']);
        $this->assertSame('content/gallery-a.avif', data_get($translated, 'content.3.data.images.0.path'));
        $this->assertSame('content/gallery-b.avif', data_get($translated, 'content.3.data.images.1.path'));
        $this->assertSame('gallery_4', data_get($translated, 'content.3.type'));
        $this->assertSame('content/gallery-d.avif', data_get($translated, 'content.3.data.images.3.path'));

        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer secret-token')
            && $request->hasHeader('X-Title', 'Green Energy Hub Tests')
        );
    }

    public function test_openrouter_connection_rejects_an_unknown_translation_model(): void
    {
        Http::fake([
            'https://openrouter.test/api/v1/models' => Http::response([
                'data' => [['id' => 'google/gemma-4-26b-a4b-it']],
            ], 200),
        ]);

        $service = app(AiTranslationService::class);

        $service->testConnection([
            'api_key' => 'secret-token',
            'model' => 'google/gemma-4-26b-a4b-it',
            'base_url' => 'https://openrouter.test/api/v1',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('не найдена в OpenRouter');

        $service->testConnection([
            'api_key' => 'secret-token',
            'model' => 'google/gemma-4-26b-a4b-it-typo',
            'base_url' => 'https://openrouter.test/api/v1',
        ]);
    }

    public function test_translation_preserves_rich_text_paragraph_breaks(): void
    {
        SiteSetting::putEncrypted('ai.openrouter_api_key', 'secret-token');
        SiteSetting::putValue('ai.openrouter_model', 'test/model');
        SiteSetting::putValue('ai.openrouter_base_url', 'https://openrouter.test/api/v1');

        Http::fake([
            'https://openrouter.test/api/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'title' => 'Titlu tradus',
                        'excerpt' => 'Descriere tradus',
                        'content' => [[
                            'type' => 'paragraph',
                            'data' => [
                                // A plain-text fallback must still become two
                                // editor paragraphs, not one long paragraph.
                                'text' => "Primul paragraf tradus.\n\nAl doilea paragraf tradus.",
                            ],
                        ]],
                    ], JSON_UNESCAPED_UNICODE)],
                ]],
            ], 200),
        ]);

        $source = [
            'title' => 'Заголовок',
            'excerpt' => 'Краткое описание',
            'content' => [[
                'type' => 'paragraph',
                'data' => ['text' => [
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'Первый абзац.']],
                        ],
                        [
                            'type' => 'paragraph',
                            'content' => [['type' => 'text', 'text' => 'Второй абзац.']],
                        ],
                    ],
                ]],
            ]],
        ];

        $translated = app(AiTranslationService::class)->translateFromRussian($source, 'ro');

        $this->assertSame(
            '<p>Primul paragraf tradus.</p><p>Al doilea paragraf tradus.</p>',
            data_get($translated, 'content.0.data.text'),
        );

        Http::assertSent(function ($request): bool {
            $messages = $request->data()['messages'] ?? [];
            $sourcePayload = (string) data_get($messages, '1.content');

            return str_contains($sourcePayload, '<p>Первый абзац.</p>')
                && str_contains($sourcePayload, '<p>Второй абзац.</p>')
                && str_contains((string) data_get($messages, '0.content'), 'separate <p> elements');
        });
    }

    public function test_translation_rejects_unsupported_languages_and_changed_block_counts(): void
    {
        SiteSetting::putEncrypted('ai.openrouter_api_key', 'secret-token');
        SiteSetting::putValue('ai.openrouter_model', 'test/model');

        $service = app(AiTranslationService::class);

        $this->expectException(\RuntimeException::class);
        $service->translateFromRussian(['title' => 'Title', 'excerpt' => '', 'content' => []], 'ru');
    }

    public function test_translation_preserves_image_text_preset_paths(): void
    {
        SiteSetting::putEncrypted('ai.openrouter_api_key', 'secret-token');
        SiteSetting::putValue('ai.openrouter_model', 'test/model');
        SiteSetting::putValue('ai.openrouter_base_url', 'https://openrouter.test/api/v1');

        Http::fake([
            'https://openrouter.test/api/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'title' => 'Titlu',
                        'excerpt' => 'Descriere',
                        'content' => [[
                            'type' => 'image_text_photo_left',
                            'data' => ['path' => 'wrong-path.avif', 'text' => 'Text tradus'],
                        ]],
                    ], JSON_UNESCAPED_UNICODE)],
                ]],
            ], 200),
        ]);

        $translated = app(AiTranslationService::class)->translateFromRussian([
            'title' => 'Заголовок',
            'excerpt' => 'Описание',
            'content' => [[
                'type' => 'image_text_photo_left',
                'data' => ['path' => 'content/source.avif', 'text' => 'Русский текст'],
            ]],
        ], 'ro');

        $this->assertSame('image_text_photo_left', data_get($translated, 'content.0.type'));
        $this->assertSame('content/source.avif', data_get($translated, 'content.0.data.path'));
        $this->assertSame('Text tradus', data_get($translated, 'content.0.data.text'));
    }

    public function test_translation_rejects_a_response_that_changes_the_block_count(): void
    {
        SiteSetting::putEncrypted('ai.openrouter_api_key', 'secret-token');
        SiteSetting::putValue('ai.openrouter_model', 'test/model');
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'title' => 'Translated',
                        'excerpt' => 'Translated excerpt',
                        'content' => [],
                    ])],
                ]],
            ], 200),
        ]);

        $this->expectException(\RuntimeException::class);
        app(AiTranslationService::class)->translateFromRussian([
            'title' => 'Title',
            'excerpt' => 'Excerpt',
            'content' => [['type' => 'paragraph', 'data' => ['text' => 'Text']]],
        ], 'en');
    }

    public function test_translation_hash_ignores_builder_keys_but_detects_source_changes(): void
    {
        $sourceWithBuilderKeys = [
            'title' => 'Русский заголовок',
            'excerpt' => 'Русское описание',
            'content' => [
                'builder-key-a' => [
                    'type' => 'paragraph',
                    'data' => ['text' => '<p>Текст материала</p>'],
                ],
                'builder-key-b' => [
                    'type' => 'gallery_2',
                    'data' => ['images' => [
                        'image-key-a' => ['path' => 'content/one.avif'],
                        'image-key-b' => ['path' => 'content/two.avif'],
                    ]],
                ],
            ],
        ];

        $sameSavedSource = [
            'title' => 'Русский заголовок',
            'excerpt' => 'Русское описание',
            'content' => [
                ['type' => 'paragraph', 'data' => ['text' => '<p>Текст материала</p>']],
                ['type' => 'gallery_2', 'data' => ['images' => [
                    ['path' => 'content/one.avif'],
                    ['path' => 'content/two.avif'],
                ]]],
            ],
        ];

        $changedSource = $sameSavedSource;
        $changedSource['title'] = 'Изменённый заголовок';

        $this->assertSame(
            NewsResource::translationHash($sourceWithBuilderKeys),
            NewsResource::translationHash($sameSavedSource),
        );
        $this->assertNotSame(
            NewsResource::translationHash($sameSavedSource),
            NewsResource::translationHash($changedSource),
        );

        $richEditorSource = $sameSavedSource;
        $richEditorSource['content'][0]['data']['text'] = [
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'Текст редактора']],
            ]],
        ];

        $this->assertNotEmpty(NewsResource::translationHash($richEditorSource));
        $this->assertStringContainsString('editorial-translation-complete', file_get_contents(public_path('js/filament-admin.js')));
        $this->assertStringContainsString('refreshEditorialTranslationStatus', file_get_contents(public_path('js/filament-admin.js')));
        $this->assertStringContainsString('component.$refresh()', file_get_contents(public_path('js/filament-admin.js')));
    }

    public function test_filament_image_previews_use_domain_independent_storage_urls(): void
    {
        $this->assertSame(
            '/storage/uploads/albums/cover%20image.avif',
            FilamentImageUpload::relativePublicUrl('uploads/albums/cover image.avif'),
        );
        $this->assertStringNotContainsString(config('app.url'), FilamentImageUpload::relativePublicUrl('content/example.avif'));
        $this->assertStringNotContainsString('GreenEnergyFilePondPreview', file_get_contents(public_path('js/filament-admin.js')));
        $this->assertStringNotContainsString('styleItemPanelAspectRatio', file_get_contents(public_path('js/filament-admin.js')));
        $this->assertStringNotContainsString('pond.browse()', file_get_contents(public_path('js/filament-admin.js')));
        $this->assertStringNotContainsString('--green-energy-filepond-ratio', file_get_contents(public_path('js/filament-admin.js')));
        $this->assertStringNotContainsString('fi-natural-image-preview', file_get_contents(public_path('js/filament-admin.js')));
        $this->assertStringNotContainsString('fi-natural-image-preview', file_get_contents(public_path('css/filament-admin.css')));
        $this->assertStringContainsString('[data-filepond-item-state*="processing-complete"] .filepond--file-info', file_get_contents(public_path('css/filament-admin.css')));
        $this->assertStringContainsString('.filepond--image-bitmap', file_get_contents(public_path('css/filament-admin.css')));
        $this->assertStringContainsString('.editorial-cover-upload .filepond--root', file_get_contents(public_path('css/filament-admin.css')));
        $this->assertStringContainsString('.album-cover-upload .filepond--root', file_get_contents(public_path('css/filament-admin.css')));
        $this->assertStringContainsString('width: 33.3333%', file_get_contents(public_path('css/filament-admin.css')));
        $this->assertStringContainsString('max-width: 33.3333%', file_get_contents(public_path('css/filament-admin.css')));
        $this->assertStringNotContainsString('object-fit: contain', file_get_contents(public_path('css/filament-admin.css')));
        $this->assertStringContainsString('object-fit: cover', file_get_contents(public_path('css/filament-admin.css')));
    }

    public function test_saved_editorial_gallery_images_are_loaded_again_on_edit(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        Storage::fake('public');

        $galleryThree = [
            'content/saved-three-a.avif',
            'content/saved-three-b.avif',
            'content/saved-three-c.avif',
        ];
        $galleryFour = [
            'content/saved-four-a.avif',
            'content/saved-four-b.avif',
            'content/saved-four-c.avif',
            'content/saved-four-d.avif',
        ];

        foreach ([...$galleryThree, ...$galleryFour] as $path) {
            Storage::disk('public')->put($path, UploadedFile::fake()->image(basename($path), 800, 600)->getContent());
        }

        $news = News::create([
            'slug' => 'saved-galleries-load',
            'status' => 'draft',
            'title' => ['ru' => 'Галереи после сохранения'],
            'excerpt' => ['ru' => 'Проверка повторной загрузки изображений.'],
            'content' => ['ru' => [
                ['type' => 'gallery_3', 'data' => ['images' => array_map(fn (string $path): array => ['path' => $path], $galleryThree)]],
                ['type' => 'gallery_4', 'data' => ['images' => array_map(fn (string $path): array => ['path' => $path], $galleryFour)]],
            ]],
        ]);

        $component = Livewire::actingAs($admin)
            ->test(EditNews::class, ['record' => $news->id]);
        $uploads = array_values(array_filter(
            $component->instance()->getSchema('form')->getFlatComponents(withActions: false, withHidden: true),
            fn (mixed $field): bool => $field instanceof FileUpload,
        ));
        $galleryUploads = array_values(array_filter(
            $uploads,
            fn (FileUpload $upload): bool => str_contains($upload->getStatePath(), 'content.ru'),
        ));

        $this->assertCount(7, $galleryUploads);
        foreach ($galleryUploads as $upload) {
            $uploadedFiles = $upload->getUploadedFiles() ?? [];
            $this->assertNotEmpty($uploadedFiles);
            $this->assertNotEmpty(array_values($uploadedFiles)[0]['url'] ?? null);
        }
    }

    public function test_newly_uploaded_editorial_gallery_images_are_saved_and_loaded_again(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        Storage::fake('public');
        $threeBlock = (string) Str::uuid();
        $fourBlock = (string) Str::uuid();
        $threeImages = array_fill_keys(array_map(fn (): string => (string) Str::uuid(), range(1, 3)), ['path' => null]);
        $fourImages = array_fill_keys(array_map(fn (): string => (string) Str::uuid(), range(1, 4)), ['path' => null]);

        $component = Livewire::actingAs($admin)
            ->test(CreateNews::class)
            ->fillForm([
                'title.ru' => 'Галереи после сохранения',
                'excerpt.ru' => 'Проверка сохранения изображений в галереях.',
                'content.ru' => [
                    $threeBlock => ['type' => 'gallery_3', 'data' => ['images' => $threeImages]],
                    $fourBlock => ['type' => 'gallery_4', 'data' => ['images' => $fourImages]],
                ],
            ]);

        $galleryUploads = array_values(array_filter(
            $component->instance()->getSchema('form')->getFlatComponents(withActions: false, withHidden: true),
            fn (mixed $field): bool => $field instanceof FileUpload && str_contains($field->getStatePath(), 'content.ru'),
        ));

        $this->assertCount(7, $galleryUploads);

        foreach ($galleryUploads as $index => $upload) {
            $width = $index < 3 ? 800 : 600;
            $height = $index < 3 ? 600 : 800;

            $component->upload(
                $upload->getStatePath(),
                [UploadedFile::fake()->image("gallery-{$index}.jpg", $width, $height)],
            );
        }

        $component->call('saveDraft')->assertHasNoFormErrors();

        $news = News::query()->latest('id')->firstOrFail();
        $savedPaths = collect($news->contentFor('ru'))
            ->flatMap(fn (array $block): array => array_map(
                fn (array $image): ?string => $image['path'] ?? null,
                data_get($block, 'data.images', []),
            ))
            ->filter();

        $this->assertCount(7, $savedPaths);
        $savedPaths->each(fn (string $path) => Storage::disk('public')->assertExists($path));

        $edit = Livewire::actingAs($admin)
            ->test(EditNews::class, ['record' => $news->id]);
        $editUploads = array_values(array_filter(
            $edit->instance()->getSchema('form')->getFlatComponents(withActions: false, withHidden: true),
            fn (mixed $field): bool => $field instanceof FileUpload && str_contains($field->getStatePath(), 'content.ru'),
        ));

        $this->assertCount(7, $editUploads);
        foreach ($editUploads as $upload) {
            $uploadedFiles = $upload->getUploadedFiles() ?? [];
            $this->assertNotEmpty($uploadedFiles);
            $this->assertNotEmpty(array_values($uploadedFiles)[0]['url'] ?? null);
        }
    }

    public function test_avif_processor_resizes_images_using_database_settings(): void
    {
        if (! function_exists('imageavif')) {
            $this->fail('The GD extension with AVIF support is required for image processing.');
        }

        Storage::fake('public');
        SiteSetting::putValue('images.max_dimension', 320);
        SiteSetting::putValue('images.avif_quality', 60);

        $path = app(ImageProcessor::class)->store(
            UploadedFile::fake()->image('large.jpg', 1200, 600),
            'content',
        );

        Storage::disk('public')->assertExists($path);
        $processed = Image::read(Storage::disk('public')->path($path));

        $this->assertStringEndsWith('.avif', $path);
        $this->assertSame(320, $processed->width());
        $this->assertSame(160, $processed->height());
    }

    public function test_avif_processor_crops_images_to_the_same_ratio_as_the_editor(): void
    {
        if (! function_exists('imageavif')) {
            $this->fail('The GD extension with AVIF support is required for image processing.');
        }

        Storage::fake('public');
        SiteSetting::putValue('images.max_dimension', 2400);
        SiteSetting::putValue('images.avif_quality', 60);

        $path = app(ImageProcessor::class)->store(
            UploadedFile::fake()->image('wide.jpg', 1200, 600),
            'content',
            FilamentImageUpload::LANDSCAPE_RATIO,
        );

        $processed = Image::read(Storage::disk('public')->path($path));

        $this->assertSame(800, $processed->width());
        $this->assertSame(600, $processed->height());
    }

    public function test_avif_processor_handles_large_gallery_sources_before_cropping(): void
    {
        if (! function_exists('imageavif')) {
            $this->fail('The GD extension with AVIF support is required for image processing.');
        }

        Storage::fake('public');
        SiteSetting::putValue('images.max_dimension', 640);
        SiteSetting::putValue('images.avif_quality', 60);

        $path = app(ImageProcessor::class)->store(
            UploadedFile::fake()->image('large-gallery-source.jpg', 3200, 1800),
            'uploads/albums',
            FilamentImageUpload::LANDSCAPE_RATIO,
        );

        $processed = Image::read(Storage::disk('public')->path($path));

        $this->assertSame(480, $processed->width());
        $this->assertSame(360, $processed->height());
    }

    public function test_filament_navigation_groups_and_entries_remain_explicit(): void
    {
        $panel = new AdminPanelProvider(app());
        $panel = $panel->panel(Panel::make());

        $this->assertFalse($panel->hasDarkMode());
        $this->assertFalse($panel->hasThemeSwitcher());
        $this->assertSame([
            'Новости',
            'Тендеры',
            'Медиа',
            'Настройки сайта',
        ], $panel->getNavigationGroups());

        $this->assertNavigationItems(
            NewsResource::getNavigationItems(),
            ['news.index', 'news.create'],
            'Новости',
        );
        $this->assertNavigationItems(
            OpportunityResource::getNavigationItems(),
            ['opportunities.index', 'opportunities.create'],
            'Тендеры',
        );
        $this->assertNavigationItems(
            PhotoAlbumResource::getNavigationItems(),
            ['photo-albums.index', 'photo-albums.create'],
            'Медиа',
        );

        $tagItem = TagResource::getNavigationItems()[0];
        $this->assertSame('Теги', $tagItem->getLabel());
        $this->assertSame('Тендеры', $tagItem->getGroup());
    }

    public function test_filament_resource_tables_stack_on_mobile(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        foreach (['/admin/news', '/admin/opportunities', '/admin/tags', '/admin/photo-albums'] as $path) {
            $this->actingAs($admin)
                ->get($path)
                ->assertOk()
                ->assertSee('fi-ta-table-stacked-on-mobile', false);
        }
    }

    public function test_photo_album_table_loads_cover_images_from_the_public_disk(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        Storage::fake('public');

        $coverPath = 'uploads/albums/table-cover.avif';
        Storage::disk('public')->put($coverPath, UploadedFile::fake()->image('cover.jpg')->getContent());

        PhotoAlbum::create([
            'slug' => 'table-cover-preview',
            'status' => 'draft',
            'cover_image' => $coverPath,
            'title' => ['ru' => 'Альбом с обложкой'],
        ]);

        $this->actingAs($admin)
            ->get('/admin/photo-albums')
            ->assertOk()
            ->assertSee('/storage/uploads/albums/table-cover.avif', false);
    }

    public function test_admin_mobile_sidebar_has_a_dedicated_overlay_layer(): void
    {
        $css = file_get_contents(public_path('css/filament-admin.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.fi-panel-admin .fi-sidebar-close-overlay', $css);
        $this->assertStringContainsString('z-index: 55', $css);
        $this->assertStringContainsString('.fi-panel-admin .fi-sidebar {', $css);
        $this->assertStringContainsString('z-index: 60', $css);
        $this->assertStringContainsString(':has(.fi-sidebar.fi-sidebar-open)', $css);
    }

    public function test_admin_routes_are_protected_and_configured_admin_can_access_them(): void
    {
        Config::set('admin.email', 'admin@example.test');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Войти')
            ->assertSee('Адрес электронной почты')
            ->assertSee('Пароль');

        $this->get('/admin/news')->assertRedirect('/admin/login');

        $admin = User::factory()->create(['email' => 'admin@example.test']);

        $this->actingAs($admin)
            ->get('/admin/news')
            ->assertOk()
            ->assertSee('css/filament-admin.css');

        $this->actingAs($admin)
            ->get('/admin/ai-connection')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/photo-albums/create')
            ->assertOk()
            ->assertSee('Новый фотоальбом')
            ->assertSee('Название альбома')
            ->assertSee('Обложка альбома');

        $newsCreateResponse = $this->actingAs($admin)
            ->get('/admin/news/create');

        $newsCreateResponse
            ->assertOk()
            ->assertSee('Новая новость')
            ->assertSee('Фотография обложки')
            ->assertDontSee('Материал')
            ->assertDontSee('Публикация')
            ->assertDontSee('Черновик ещё не сохранён')
            ->assertSee('Языковые версии')
            ->assertSee('Русский')
            ->assertSee('Română')
            ->assertSee('English')
            ->assertDontSee('Основной текст')
            ->assertSee('0 / 100 символов')
            ->assertSee('Сохранить черновик')
            ->assertSee('Предпросмотр')
            ->assertSee('Опубликовать');

        $newsCreateResponse
            ->assertDontSee('>Text<', false)
            ->assertDontSee('>Images<', false);

        $this->assertStringContainsString('editorial-content-builder', $newsCreateResponse->getContent());
        $this->assertStringContainsString('js/filament-admin.js', $newsCreateResponse->getContent());

        $this->assertLessThan(
            strpos($newsCreateResponse->getContent(), 'Фотография обложки'),
            strpos($newsCreateResponse->getContent(), 'Краткое описание'),
        );

        $opportunityCreateResponse = $this->actingAs($admin)
            ->get('/admin/opportunities/create');

        $opportunityCreateResponse
            ->assertOk()
            ->assertSee('Новый тендер')
            ->assertSee('Фотография обложки')
            ->assertDontSee('Материал')
            ->assertDontSee('Публикация')
            ->assertDontSee('Черновик ещё не сохранён')
            ->assertSee('Языковые версии')
            ->assertSee('Русский')
            ->assertSee('Română')
            ->assertSee('English')
            ->assertDontSee('Основной текст')
            ->assertSee('0 / 100 символов')
            ->assertSee('Сохранить черновик')
            ->assertSee('Предпросмотр')
            ->assertSee('Опубликовать');

        $opportunityCreateResponse
            ->assertDontSee('>Text<', false)
            ->assertDontSee('>Images<', false);

        $this->assertLessThan(
            strpos($opportunityCreateResponse->getContent(), 'Фотография обложки'),
            strpos($opportunityCreateResponse->getContent(), 'Краткое описание'),
        );

        $this->get('/')->assertDontSee('filament-admin.css');
    }

    public function test_filament_create_forms_keep_the_required_multilingual_fields(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        Storage::fake('public');
        Storage::disk('public')->put('uploads/covers/demo.avif', UploadedFile::fake()->image('demo.jpg')->getContent());

        $this->actingAs($admin);

        Livewire::test(CreateNews::class)
            ->assertFormFieldExists('slug')
            ->assertFormFieldExists('excerpt.ru', function (Textarea $field): bool {
                return $field->getMaxLength() === ContentLimits::SHORT_DESCRIPTION_MAX
                    && $field->hasHint();
            })
            ->assertFormFieldExists('title.ru')
            ->assertFormFieldExists('title.ro')
            ->assertFormFieldExists('title.en')
            ->assertFormFieldExists('content.ru', function ($field): bool {
                $singleImage = $field->getBlock('image')?->getChildSchema()->getComponents()[0] ?? null;
                $galleryTwo = $field->getBlock('gallery_2')?->getChildSchema()->getComponents()[0] ?? null;
                $galleryThree = $field->getBlock('gallery_3')?->getChildSchema()->getComponents()[0] ?? null;
                $galleryFour = $field->getBlock('gallery_4')?->getChildSchema()->getComponents()[0] ?? null;
                $galleryTwoUpload = $galleryTwo instanceof Repeater
                    ? $galleryTwo->getChildSchema()->getComponents()[0] ?? null
                    : null;
                $galleryThreeUpload = $galleryThree instanceof Repeater
                    ? $galleryThree->getChildSchema()->getComponents()[0] ?? null
                    : null;
                $galleryFourUpload = $galleryFour instanceof Repeater
                    ? $galleryFour->getChildSchema()->getComponents()[0] ?? null
                    : null;
                $photoLeftGrid = $field->getBlock('image_text_photo_left')?->getChildSchema()->getComponents()[0] ?? null;
                $photoLeftImage = $photoLeftGrid instanceof Grid
                    ? $photoLeftGrid->getChildSchema()->getComponents()[0] ?? null
                    : null;

                return $field->getAddActionLabel() === 'Добавить блок'
                    && $field->getAddBetweenActionLabel() === 'Вставить между'
                    && ! $field->hasBlockNumbers()
                    && $field->getBlock('paragraph')?->getIcon() === Heroicon::OutlinedDocumentText
                    && $singleImage instanceof FileUpload
                    && $singleImage->getPanelAspectRatio() === FilamentImageUpload::ARTICLE_RATIO
                    && $singleImage->getItemPanelAspectRatio() === (16 / 9)
                    && $singleImage->getImageCropAspectRatio() === FilamentImageUpload::ARTICLE_RATIO
                    && $photoLeftImage instanceof FileUpload
                    && $photoLeftImage->getPanelAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                    && $photoLeftImage->getItemPanelAspectRatio() === (4 / 3)
                    && $photoLeftImage->getImageCropAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                    && $galleryTwo instanceof Repeater
                    && $galleryTwo->getMinItems() === 2
                    && $galleryTwo->getMaxItems() === 2
                    && ! $galleryTwo->isAddable()
                    && $galleryTwoUpload instanceof FileUpload
                     && ! $galleryTwoUpload->shouldFetchFileInformation()
                    && $galleryTwoUpload->getPanelLayout() === 'integrated'
                    && $galleryTwoUpload->getPanelAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                    && $galleryTwoUpload->getItemPanelAspectRatio() === (4 / 3)
                    && $galleryTwoUpload->getImageCropAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                    && $galleryThree instanceof Repeater
                    && $galleryThree->getMinItems() === 3
                    && $galleryThree->getMaxItems() === 3
                    && $galleryThreeUpload instanceof FileUpload
                    && $galleryThreeUpload->getPanelAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                    && $galleryFour instanceof Repeater
                    && $galleryFour->getMinItems() === 4
                    && $galleryFour->getMaxItems() === 4
                    && $galleryFourUpload instanceof FileUpload
                    && $galleryFourUpload->getPanelLayout() === 'integrated'
                    && $galleryFourUpload->getPanelAspectRatio() === FilamentImageUpload::PORTRAIT_RATIO
                    && $galleryFourUpload->getItemPanelAspectRatio() === 0.75
                    && $galleryFourUpload->getImageCropAspectRatio() === FilamentImageUpload::PORTRAIT_RATIO
                    && $field->getBlock('video')?->getIcon() === Heroicon::OutlinedVideoCamera;
            })
            ->assertFormFieldExists('content.ro')
            ->assertFormFieldExists('content.en');

        Livewire::test(CreateNews::class)
            ->assertFormFieldExists('published_at')
            ->assertActionExists('saveDraft')
            ->assertActionExists('schedulePublication')
            ->assertActionExists('publishNow')
            ->assertActionExists('cancel');

        Livewire::test(CreateOpportunity::class)
            ->assertFormFieldExists('published_at')
            ->assertFormFieldExists('application_deadline')
            ->assertFormFieldExists('excerpt.ru', function (Textarea $field): bool {
                return $field->getMaxLength() === ContentLimits::SHORT_DESCRIPTION_MAX
                    && $field->hasHint();
            })
            ->assertFormFieldExists('tag_id', function (FormSelect $field): bool {
                return ! $field->isMultiple();
            })
            ->assertFormFieldExists('title.ru')
            ->assertFormFieldExists('content.en');

        Livewire::test(CreateOpportunity::class)
            ->assertActionExists('saveDraft')
            ->assertActionExists('schedulePublication')
            ->assertActionExists('publishNow')
            ->assertActionExists('cancel');

        Livewire::test(CreatePhotoAlbum::class)
            ->assertFormFieldExists('title.ru')
            ->assertFormFieldExists('title.ro')
            ->assertFormFieldExists('title.en')
            ->assertFormFieldExists('status', function (FormSelect $field): bool {
                return $field->getDefaultState() === 'published';
            })
            ->assertFormFieldExists('cover_image', fn (FileUpload $field): bool => $field->getImagePreviewHeight() === '120'
                && $field->getPanelAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                && $field->getItemPanelAspectRatio() === (4 / 3)
                && $field->getImageCropAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO)
            ->assertFormFieldExists('photo_content', function ($field): bool {
                $galleryTwo = $field->getBlock('gallery_2')?->getChildSchema()->getComponents()[0] ?? null;
                $galleryThree = $field->getBlock('gallery_3')?->getChildSchema()->getComponents()[0] ?? null;
                $galleryFour = $field->getBlock('gallery_4')?->getChildSchema()->getComponents()[0] ?? null;
                $galleryTwoUpload = $galleryTwo instanceof Repeater
                    ? $galleryTwo->getChildSchema()->getComponents()[0] ?? null
                    : null;
                $galleryThreeUpload = $galleryThree instanceof Repeater
                    ? $galleryThree->getChildSchema()->getComponents()[0] ?? null
                    : null;
                $galleryFourUpload = $galleryFour instanceof Repeater
                    ? $galleryFour->getChildSchema()->getComponents()[0] ?? null
                    : null;

                return $field->getBlock('image') !== null
                    && $field->getBlock('gallery_2') !== null
                    && $field->getBlock('gallery_3') !== null
                    && $field->getBlock('gallery_4') !== null
                    && $field->getBlock('paragraph') === null
                    && $galleryTwoUpload instanceof FileUpload
                    && ! $galleryTwoUpload->shouldFetchFileInformation()
                    && $galleryTwoUpload->getPanelLayout() === 'integrated'
                    && $galleryTwoUpload->getPanelAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                    && $galleryTwoUpload->getImageCropAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                    && $galleryThreeUpload instanceof FileUpload
                    && ! $galleryThreeUpload->shouldFetchFileInformation()
                    && $galleryThreeUpload->getPanelLayout() === 'integrated'
                    && $galleryThreeUpload->getPanelAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                    && $galleryThreeUpload->getImageCropAspectRatio() === FilamentImageUpload::LANDSCAPE_RATIO
                    && $galleryFourUpload instanceof FileUpload
                    && ! $galleryFourUpload->shouldFetchFileInformation()
                    && $galleryFourUpload->getPanelLayout() === 'integrated'
                    && $galleryFourUpload->getPanelAspectRatio() === FilamentImageUpload::PORTRAIT_RATIO
                    && $galleryFourUpload->getImageCropAspectRatio() === FilamentImageUpload::PORTRAIT_RATIO;
            });

        $this->assertSame('published', PhotoAlbumResource::mutateFormData([])['status']);

        Livewire::test(CreateTag::class)
            ->assertFormFieldExists('name.ru', fn ($field): bool => $field->isRequired())
            ->assertFormFieldExists('name.ro', fn ($field): bool => $field->isRequired())
            ->assertFormFieldExists('name.en', fn ($field): bool => $field->isRequired())
            ->assertFormFieldExists('color', function (FormSelect $field): bool {
                return $field->isHtmlAllowed()
                    && str_contains($field->getOptions()['#DDF6B7'], 'background-color: #DDF6B7')
                    && array_keys($field->getOptions()) === [
                        '#DDF6B7',
                        '#C6E3FF',
                    '#E5D8B5',
                ];
            });

        foreach ([CreateNews::class, CreateOpportunity::class, CreatePhotoAlbum::class, CreateTag::class] as $page) {
            $this->assertFalse(Livewire::test($page)->instance()->canCreateAnother());
        }

        $this->assertSame(
            ['Зелёный', 'Голубой', 'Песочный'],
            array_values(Tag::colorOptions()),
        );

        $tag = Tag::create([
            'name' => ['ru' => 'Песочный', 'ro' => 'Nisipiu', 'en' => 'Sand'],
            'color' => '#E5D8B5',
        ]);

        $this->assertSame('Песочный', $tag->colorLabel());
    }

    public function test_filament_can_create_a_draft_with_a_generated_slug(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        Livewire::actingAs($admin)
            ->test(CreateNews::class)
            ->fillForm([
                'title.ru' => 'Green Energy Hub',
                'excerpt.ru' => 'Краткое описание',
                'content.ru' => [[
                    'type' => 'paragraph',
                    'data' => ['text' => '<p>Текст публикации</p>'],
                ]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news', [
            'slug' => 'green-energy-hub',
            'status' => 'draft',
        ]);
    }

    public function test_editorial_workflow_allows_light_drafts_and_validates_only_on_publication(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        Livewire::actingAs($admin)
            ->test(CreateNews::class)
            ->fillForm(['title.ru' => 'Черновая заметка'])
            ->call('saveDraft')
            ->assertHasNoFormErrors();

        $news = News::query()->latest('id')->firstOrFail();
        $this->assertSame('draft', $news->status);
        $this->assertSame('cernovaia-zametka', $news->slug);

        Livewire::actingAs($admin)
            ->test(EditNews::class, ['record' => $news->id])
            ->call('publishNow')
            ->assertHasErrors(['data.excerpt.ru']);

        Livewire::actingAs($admin)
            ->test(EditNews::class, ['record' => $news->id])
            ->fillForm([
                'excerpt.ru' => 'Краткое описание',
            ])
            ->call('publishNow')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news', [
            'id' => $news->id,
            'status' => 'published',
            'cover_image' => null,
        ]);
    }

    public function test_create_page_actions_update_autosaved_editorial_records(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        $newsComponent = Livewire::actingAs($admin)
            ->test(CreateNews::class)
            ->fillForm(['title.ru' => 'Автосохранённая новость'])
            ->call('autosaveDraft')
            ->fillForm(['excerpt.ru' => 'Короткое описание'])
            ->call('publishNow')
            ->assertHasNoFormErrors();

        $newsComponent->assertStatus(200);

        $this->assertDatabaseHas('news', [
            'slug' => 'avtosoxranennaia-novost',
            'status' => 'published',
        ]);
        $this->assertSame(1, News::query()->where('slug', 'avtosoxranennaia-novost')->count());

        Livewire::actingAs($admin)
            ->test(CreateOpportunity::class)
            ->fillForm(['title.ru' => 'Автосохранённая возможность'])
            ->call('autosaveDraft')
            ->fillForm(['excerpt.ru' => 'Короткое описание'])
            ->call('schedulePublication', now()->addDay()->toDateTimeString())
            ->assertHasNoFormErrors()
            ->assertStatus(200);

        $this->assertDatabaseHas('opportunities', [
            'slug' => 'avtosoxranennaia-vozmoznost',
            'status' => 'scheduled',
        ]);
        $this->assertSame(1, Opportunity::query()->where('slug', 'avtosoxranennaia-vozmoznost')->count());
    }

    public function test_published_news_publication_date_can_be_changed_without_unpublishing(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $news = News::create([
            'slug' => 'published-date-edit',
            'status' => 'published',
            'published_at' => Carbon::parse('2026-08-01 09:00:00'),
            'title' => ['ru' => 'Опубликованная новость'],
            'excerpt' => ['ru' => 'Короткое описание'],
            'content' => ['ru' => [['type' => 'paragraph', 'data' => ['text' => 'Текст новости']]]],
        ]);

        Livewire::actingAs($admin)
            ->test(EditNews::class, ['record' => $news->id])
            ->assertFormFieldExists('published_at')
            ->fillForm([
                'published_at' => Carbon::parse('2026-08-12 14:30:00'),
            ])
            ->call('saveDraft')
            ->assertHasNoFormErrors();

        $news->refresh();

        $this->assertSame('published', $news->status);
        $this->assertSame('2026-08-12 14:30:00', $news->published_at?->format('Y-m-d H:i:s'));
    }

    public function test_published_opportunity_publication_date_can_be_changed_without_unpublishing(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $opportunity = Opportunity::create([
            'slug' => 'published-opportunity-date-edit',
            'status' => 'published',
            'published_at' => Carbon::parse('2026-08-01 09:00:00'),
            'title' => ['ru' => 'Опубликованный тендер'],
            'excerpt' => ['ru' => 'Краткое описание тендера'],
            'content' => ['ru' => [['type' => 'paragraph', 'data' => ['text' => 'Текст тендера']]]],
        ]);

        Livewire::actingAs($admin)
            ->test(EditOpportunity::class, ['record' => $opportunity->id])
            ->assertFormFieldExists('published_at')
            ->fillForm([
                'published_at' => Carbon::parse('2026-08-12 14:30:00'),
            ])
            ->call('saveDraft')
            ->assertHasNoFormErrors();

        $opportunity->refresh();

        $this->assertSame('published', $opportunity->status);
        $this->assertSame('2026-08-12 14:30:00', $opportunity->published_at?->format('Y-m-d H:i:s'));
    }

    public function test_new_editorial_records_can_be_backdated_but_not_published_with_a_future_date(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $backdatedAt = Carbon::parse('2026-08-10 12:30:00');

        Livewire::actingAs($admin)
            ->test(CreateNews::class)
            ->fillForm([
                'title.ru' => 'Новость с датой',
                'excerpt.ru' => 'Короткое описание новости.',
                'published_at' => $backdatedAt,
            ])
            ->call('publishNow')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news', [
            'slug' => 'novost-s-datoi',
            'status' => 'published',
            'published_at' => $backdatedAt->format('Y-m-d H:i:s'),
        ]);

        Livewire::actingAs($admin)
            ->test(CreateOpportunity::class)
            ->fillForm([
                'title.ru' => 'Возможность из будущего',
                'excerpt.ru' => 'Короткое описание возможности.',
                'published_at' => now()->addDay(),
            ])
            ->call('publishNow')
            ->assertHasErrors(['data.published_at']);

        $this->assertDatabaseMissing('opportunities', [
            'slug' => 'vozmozhnost-iz-budushhego',
        ]);
    }

    public function test_editorial_cover_is_loaded_on_edit_with_the_public_card_ratio(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        Storage::fake('public');
        $coverPath = 'uploads/covers/editorial-cover.avif';
        Storage::disk('public')->put($coverPath, UploadedFile::fake()->image('cover.jpg', 1200, 800)->getContent());
        $news = News::create([
            'slug' => 'editorial-cover-preview',
            'status' => 'draft',
            'cover_image' => $coverPath,
            'title' => ['ru' => 'Новость с обложкой'],
            'excerpt' => ['ru' => 'Короткое описание'],
            'content' => ['ru' => []],
        ]);

        Livewire::actingAs($admin)
            ->test(EditNews::class, ['record' => $news->id])
            ->assertFormSet(['cover_image' => $coverPath])
            ->assertFormFieldExists('cover_image', function ($field): bool {
                return $field->getVisibility() === 'public'
                    && ! $field->shouldFetchFileInformation()
                    && $field->getImagePreviewHeight() === null
                    && $field->getImageCropAspectRatio() === FilamentImageUpload::CARD_RATIO
                    && $field->getPanelLayout() === 'integrated'
                    && $field->getPanelAspectRatio() === FilamentImageUpload::CARD_RATIO
                    && $field->getItemPanelAspectRatio() === (11 / 5);
            });
    }

    public function test_publishing_an_existing_news_item_keeps_the_editor_selected_publication_date(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $news = News::create([
            'slug' => 'published-date-kept-on-publish',
            'status' => 'published',
            'published_at' => Carbon::parse('2026-08-01 09:00:00'),
            'title' => ['ru' => 'Опубликованная новость'],
            'excerpt' => ['ru' => 'Короткое описание'],
            'content' => ['ru' => [['type' => 'paragraph', 'data' => ['text' => 'Текст новости']]]],
        ]);

        Livewire::actingAs($admin)
            ->test(EditNews::class, ['record' => $news->id])
            ->fillForm([
                'published_at' => Carbon::parse('2026-08-12 11:45:00'),
            ])
            ->call('publishNow')
            ->assertHasNoFormErrors();

        $news->refresh();

        $this->assertSame('published', $news->status);
        $this->assertSame('2026-08-12 11:45:00', $news->published_at?->format('Y-m-d H:i:s'));
    }

    public function test_short_description_validation_clears_after_editor_corrects_the_value(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        $component = Livewire::actingAs($admin)
            ->test(CreateNews::class)
            ->fillForm([
                'title.ru' => 'Тестовая новость',
                'excerpt.ru' => str_repeat('Длинное описание. ', 8),
                'content.ru' => [[
                    'type' => 'paragraph',
                    'data' => ['text' => '<p>Текст публикации</p>'],
                ]],
            ])
            ->call('saveDraft')
            ->assertHasErrors(['data.excerpt.ru']);

        $component
            ->fillForm(['excerpt.ru' => 'Краткое описание'])
            ->assertHasNoFormErrors();
    }

    public function test_editorial_rich_editor_json_does_not_exceed_livewire_payload_depth(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        $this->assertGreaterThanOrEqual(30, config('livewire.payload.max_nesting_depth'));

        Livewire::actingAs($admin)
            ->test(CreateNews::class)
            ->fillForm([
                'title.ru' => 'Глубокий текст редактора',
                'excerpt.ru' => 'Короткое описание',
                'content.ru' => [[
                    'type' => 'paragraph',
                    'data' => ['text' => $this->richEditorDocument('Текст из визуального редактора')],
                ]],
            ])
            ->call('saveDraft')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news', [
            'slug' => 'glubokii-tekst-redaktora',
            'status' => 'draft',
        ]);

        Livewire::actingAs($admin)
            ->test(CreateOpportunity::class)
            ->fillForm([
                'title.ru' => 'Глубокая возможность редактора',
                'excerpt.ru' => 'Короткое описание',
                'content.ru' => [[
                    'type' => 'paragraph',
                    'data' => ['text' => $this->richEditorDocument('Текст возможности')],
                ]],
            ])
            ->call('saveDraft')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('opportunities', [
            'slug' => 'glubokaia-vozmoznost-redaktora',
            'status' => 'draft',
        ]);
    }

    public function test_filament_settings_pages_persist_ai_and_image_configuration(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        Livewire::actingAs($admin)
            ->test(AiConnection::class)
            ->fillForm([
                'api_key' => 'secret-from-filament',
                'model' => 'test/model',
                'base_url' => 'https://openrouter.test/api/v1/',
                'app_name' => 'Green Energy Hub',
            ])
            ->call('save');

        Livewire::actingAs($admin)
            ->test(ImageCompression::class)
            ->fillForm([
                'max_dimension' => 1800,
                'avif_quality' => 72,
            ])
            ->call('save');

        $this->assertSame('secret-from-filament', SiteSetting::getEncrypted('ai.openrouter_api_key'));
        $this->assertSame('test/model', SiteSetting::getValue('ai.openrouter_model'));
        $this->assertSame('https://openrouter.test/api/v1', SiteSetting::getValue('ai.openrouter_base_url'));
        $this->assertSame(1800, SiteSetting::getValue('images.max_dimension'));
        $this->assertSame(72, SiteSetting::getValue('images.avif_quality'));
    }

    public function test_editors_keep_both_translation_actions(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $news = News::create([
            'slug' => 'translation-actions',
            'status' => 'draft',
            'title' => ['ru' => 'Заголовок'],
            'excerpt' => ['ru' => 'Описание'],
            'content' => ['ru' => [['type' => 'paragraph', 'data' => ['text' => 'Текст']]]],
        ]);

        Livewire::actingAs($admin)
            ->test(EditNews::class, ['record' => $news->id])
            ->assertActionExists('saveDraft')
            ->assertActionExists('schedulePublication')
            ->assertActionExists('publishNow');
    }

    public function test_translation_tab_status_is_exclusive_for_current_and_stale_translations(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $source = [
            'title' => 'Заголовок',
            'excerpt' => 'Краткое описание',
            'content' => [[
                'type' => 'paragraph',
                'data' => ['text' => '<p>Текст материала.</p><p>Второй абзац.</p>'],
            ]],
        ];
        $translationMeta = [
            'ro' => ['source_hash' => NewsResource::translationHash($source)],
            'en' => ['source_hash' => NewsResource::translationHash($source).'stale'],
        ];
        $news = News::create([
            'slug' => 'translation-statuses',
            'status' => 'published',
            'published_at' => now(),
            'title' => ['ru' => $source['title'], 'ro' => 'Titlu', 'en' => 'Title'],
            'excerpt' => ['ru' => $source['excerpt'], 'ro' => 'Descriere', 'en' => 'Description'],
            'content' => ['ru' => $source['content'], 'ro' => $source['content'], 'en' => $source['content']],
            'translation_meta' => $translationMeta,
        ]);

        $component = Livewire::actingAs($admin)->test(EditNews::class, ['record' => $news->getRouteKey()]);
        $tabs = collect($component->instance()->getSchema('form')?->getComponents() ?? [])
            ->first(fn (mixed $field): bool => $field instanceof Tabs);
        $localeTabs = $tabs?->getChildSchema()->getComponents() ?? [];

        $currentBadge = $localeTabs[1]->getBadge();
        $staleBadge = $localeTabs[2]->getBadge();

        $this->assertSame("\u{2713}", $currentBadge);
        $this->assertSame("\u{26A0}", $staleBadge);
        $this->assertStringNotContainsString("\u{26A0}", $currentBadge);
        $this->assertStringNotContainsString("\u{2713}", $staleBadge);
    }

    public function test_successful_translation_changes_the_tab_warning_to_a_checkmark(): void
    {
        Config::set('admin.email', 'admin@example.test');
        SiteSetting::putEncrypted('ai.openrouter_api_key', 'secret-token');
        SiteSetting::putValue('ai.openrouter_model', 'test/model');
        SiteSetting::putValue('ai.openrouter_base_url', 'https://openrouter.test/api/v1');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        $source = [
            'title' => 'РџСЂРѕРІРµСЂРєР° РїРµСЂРµРІРѕРґР°',
            'excerpt' => 'РљСЂР°С‚РєРѕРµ РѕРїРёСЃР°РЅРёРµ',
            'content' => [['type' => 'paragraph', 'data' => ['text' => '<p>РћСЃРЅРѕРІРЅРѕР№ С‚РµРєСЃС‚.</p>']]],
        ];
        $news = News::create([
            'slug' => 'translation-badge-after-action',
            'status' => 'draft',
            'title' => ['ru' => $source['title'], 'ro' => 'Titlu vechi'],
            'excerpt' => ['ru' => $source['excerpt'], 'ro' => 'Descriere veche'],
            'content' => ['ru' => $source['content'], 'ro' => $source['content']],
            'translation_meta' => [
                'ro' => ['source_hash' => NewsResource::translationHash($source).'stale'],
            ],
        ]);

        Http::fake([
            'https://openrouter.test/api/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'title' => 'Titlu actualizat',
                        'excerpt' => 'Descriere actualizată',
                        'content' => [['type' => 'paragraph', 'data' => ['text' => '<p>Text actualizat.</p>']]],
                    ], JSON_UNESCAPED_UNICODE)],
                ]],
            ], 200),
        ]);

        $component = Livewire::actingAs($admin)
            ->test(EditNews::class, ['record' => $news->id])
            ->callAction(TestAction::make('translate_ro')->schemaComponent(true))
            ->assertFormSet(fn (array $state): bool => data_get($state, 'translation_meta.ro.source_hash') === NewsResource::translationHash($source));

        $tabs = collect($component->instance()->getSchema('form')?->getComponents() ?? [])
            ->first(fn (mixed $field): bool => $field instanceof Tabs);
        $localeTabs = $tabs?->getChildSchema()->getComponents() ?? [];

        $this->assertSame("\u{2713}", $localeTabs[1]->getBadge());
        $this->assertStringNotContainsString("\u{26A0}", $localeTabs[1]->getBadge());

        preg_match('/data-tab-key="romana::data::tab".*?<\\/button>/s', $component->html(), $romanianTab);
        $this->assertNotEmpty($romanianTab[0] ?? null);
        $this->assertStringContainsString("\u{2713}", $romanianTab[0]);
        $this->assertStringNotContainsString("\u{26A0}", $romanianTab[0]);
    }

    public function test_video_urls_accept_only_supported_youtube_hosts(): void
    {
        $this->assertSame('x0AIDgyz6Qg', YouTube::extractVideoId('https://youtu.be/x0AIDgyz6Qg'));
        $this->assertSame('VP8GqtLYr38', YouTube::extractVideoId('https://www.youtube.com/watch?v=VP8GqtLYr38'));
        $this->assertSame('mgS3xvbKI3g', YouTube::extractVideoId('https://youtube.com/shorts/mgS3xvbKI3g'));
        $this->assertNull(YouTube::extractVideoId('https://vimeo.com/123456789'));
        $this->assertNull(YouTube::extractVideoId('https://youtube.com/watch?v=invalid'));
    }

    public function test_videos_keep_manual_order_and_show_their_event_date(): void
    {
        Video::create([
            'title' => ['ru' => 'Второе видео'],
            'description' => ['ru' => 'Описание второго видео'],
            'youtube_url' => 'https://youtu.be/VP8GqtLYr38',
            'youtube_id' => 'VP8GqtLYr38',
            'event_date' => '2026-08-05',
            'position' => 2,
        ]);
        Video::create([
            'title' => ['ru' => 'Первое видео'],
            'description' => ['ru' => 'Описание первого видео'],
            'youtube_url' => 'https://youtu.be/x0AIDgyz6Qg',
            'youtube_id' => 'x0AIDgyz6Qg',
            'event_date' => '2026-08-01',
            'position' => 1,
        ]);

        $content = $this->get('/media/videos')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($content, 'data-video-id="VP8GqtLYr38"'),
            strpos($content, 'data-video-id="x0AIDgyz6Qg"'),
        );
        $this->assertStringContainsString('01.08.2026', $content);
        $this->assertStringContainsString('https://i.ytimg.com/vi/x0AIDgyz6Qg/hqdefault.jpg', $content);
        $this->assertStringNotContainsString('youtube.com', explode('<footer', $content, 2)[0]);
        $this->assertStringNotContainsString('youtu.be', explode('<footer', $content, 2)[0]);
    }

    public function test_video_resource_exposes_multilingual_fields_and_optional_fixed_cover(): void
    {
        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);

        $this->actingAs($admin);

        Livewire::test(CreateVideo::class)
            ->assertFormFieldExists('title.ru')
            ->assertFormFieldExists('title.ro')
            ->assertFormFieldExists('title.en')
            ->assertFormFieldExists('description.ru')
            ->assertFormFieldExists('youtube_url', function (TextInput $field): bool {
                $rules = $field->getValidationRules();

                return $field->isRequired()
                    && $field->getMaxLength() === 2048
                    && count($rules) > 0;
            })
            ->assertFormFieldExists('event_date')
            ->assertFormFieldExists('cover_image', function (FileUpload $field): bool {
                return $field->getPanelLayout() === 'integrated'
                    && $field->getPanelAspectRatio() === FilamentImageUpload::ARTICLE_RATIO
                    && $field->getItemPanelAspectRatio() === (16 / 9)
                    && $field->getImageCropAspectRatio() === FilamentImageUpload::ARTICLE_RATIO;
            });

        $this->assertNavigationItems(
            VideoResource::getNavigationItems(),
            ['videos.index', 'videos.create'],
            'Медиа',
        );
    }

    public function test_existing_video_can_save_a_new_cover_without_filament_validation_resolution_errors(): void
    {
        if (! function_exists('imageavif')) {
            $this->fail('The GD extension with AVIF support is required for image processing.');
        }

        Config::set('admin.email', 'admin@example.test');
        $admin = User::factory()->create(['email' => 'admin@example.test']);
        Storage::fake('public');
        $video = Video::create([
            'title' => ['ru' => 'Видео для проверки обложки'],
            'description' => ['ru' => 'Описание видео'],
            'youtube_url' => 'https://youtu.be/x0AIDgyz6Qg',
            'youtube_id' => 'x0AIDgyz6Qg',
            'event_date' => '2026-08-01',
        ]);

        Livewire::actingAs($admin)
            ->test(EditVideo::class, ['record' => $video->id])
            ->upload('data.cover_image', [UploadedFile::fake()->image('video-cover.jpg', 1600, 900)])
            ->call('save')
            ->assertHasNoFormErrors();

        $video->refresh();

        $this->assertNotEmpty($video->cover_image);
        Storage::disk('public')->assertExists($video->cover_image);
    }

    /**
     * @param  array<int, NavigationItem>  $items
     * @param  array<int, string>  $keys
     */
    private function assertNavigationItems(array $items, array $keys, string $group): void
    {
        $this->assertSame($keys, array_map(
            fn (NavigationItem $item): string => $item->getKey(),
            $items,
        ));

        foreach ($items as $item) {
            $this->assertSame($group, $item->getGroup());
            $this->assertNotEmpty($item->getUrl());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function richEditorDocument(string $text): array
    {
        return [
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => $text,
                ]],
            ]],
        ];
    }
}
