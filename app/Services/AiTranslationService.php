<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Support\RichText;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiTranslationService
{
    public function testConnection(?array $overrides = null): void
    {
        $settings = $this->settings($overrides);

        if (blank($settings['api_key'])) {
            throw new RuntimeException('Ключ OpenRouter не указан.');
        }

        if (blank($settings['model'])) {
            throw new RuntimeException('Модель перевода не указана.');
        }

        $response = Http::withToken($settings['api_key'])
            ->acceptJson()
            ->timeout(15)
            ->get(rtrim($settings['base_url'], '/').'/models');

        if ($response->failed()) {
            throw new RuntimeException('OpenRouter вернул ошибку '.$response->status().'.');
        }

        $models = $response->json('data');

        if (! is_array($models)) {
            throw new RuntimeException('OpenRouter вернул список моделей в неожиданном формате.');
        }

        $modelExists = collect($models)->contains(
            static fn (mixed $model): bool => is_array($model)
                && ($model['id'] ?? null) === $settings['model'],
        );

        if (! $modelExists) {
            throw new RuntimeException("Модель «{$settings['model']}» не найдена в OpenRouter. Проверьте точное имя модели.");
        }
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array{title: string, excerpt: string, content: array<int, array<string, mixed>>}
     */
    public function translateFromRussian(array $source, string $targetLocale, ?array $overrides = null): array
    {
        if (! in_array($targetLocale, ['ro', 'en'], true)) {
            throw new RuntimeException('Автоматический перевод доступен только на румынский и английский языки.');
        }

        $settings = $this->settings($overrides);

        if (blank($settings['api_key'])) {
            throw new RuntimeException('Ключ OpenRouter не указан.');
        }

        $payload = [
            'title' => (string) ($source['title'] ?? ''),
            'excerpt' => (string) ($source['excerpt'] ?? ''),
            'content' => array_map(function (array $block): array {
                $data = $block['data'] ?? [];

                return [
                    'type' => $block['type'] ?? 'paragraph',
                    'data' => [
                        // Keep the editor's HTML structure during translation.
                        // Converting RichEditor state to plain text collapses
                        // paragraphs and makes the translated block a single
                        // paragraph when it is hydrated again.
                        'text' => RichText::toHtml($data['text'] ?? null),
                        'items' => $data['items'] ?? null,
                        'images' => array_map(
                            static fn (array $_): array => [],
                            $data['images'] ?? [],
                        ),
                    ],
                ];
            }, $source['content'] ?? []),
        ];

        $language = $targetLocale === 'ro' ? 'Romanian' : 'English';
        $response = Http::withToken($settings['api_key'])
            ->acceptJson()
            ->timeout(90)
            ->withHeaders([
                'HTTP-Referer' => request()->getSchemeAndHttpHost() ?: config('app.url'),
                'X-Title' => $settings['app_name'],
            ])
            ->post(rtrim($settings['base_url'], '/').'/chat/completions', [
                'model' => $settings['model'],
                'temperature' => 0.2,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You translate editorial content from Russian to {$language}. Return only valid JSON. Keep the content array in exactly the same order and preserve every block type. Translate only title, excerpt, text and items. For every text value, preserve the HTML structure exactly: keep paragraph boundaries as separate <p> elements, keep line breaks, and preserve only these formatting tags when present: <strong>, <em>, <a>, <ul>, <ol>, <li>, <blockquote>, <br>. Return translated rich text as HTML, never as one flattened plain-text paragraph. Do not add, remove or reorder blocks. For gallery blocks, preserve the images array with exactly the same number of items. Image block paths are handled by the application.",
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenRouter вернул ошибку '.$response->status().'.');
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content');
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content)) ?? trim($content);
        $translated = json_decode($content, true);

        if (! is_array($translated) || ! isset($translated['title'], $translated['excerpt'], $translated['content']) || ! is_array($translated['content'])) {
            throw new RuntimeException('OpenRouter вернул ответ в неожиданном формате.');
        }

        $translated['content'] = $this->preserveImageBlocks($source['content'] ?? [], $translated['content']);
        $translated['content'] = $this->normalizeRichTextBlocks($source['content'] ?? [], $translated['content']);

        return [
            'title' => (string) $translated['title'],
            'excerpt' => (string) $translated['excerpt'],
            'content' => $translated['content'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $source
     * @param  array<int, array<string, mixed>>  $translated
     * @return array<int, array<string, mixed>>
     */
    protected function preserveImageBlocks(array $source, array $translated): array
    {
        if (count($source) !== count($translated)) {
            throw new RuntimeException('Переводчик изменил структуру блоков статьи. Перевод не применён.');
        }

        foreach ($source as $index => $block) {
            $translated[$index]['type'] = $block['type'] ?? 'paragraph';

            if (! isset($translated[$index]['data']) || ! is_array($translated[$index]['data'])) {
                $translated[$index]['data'] = $block['data'] ?? [];
            }

            if (in_array($block['type'] ?? null, ['image', 'image_text_photo_left', 'image_text_text_left'], true)) {
                $translated[$index]['data']['path'] = data_get($block, 'data.path');
            }

            if (in_array($block['type'] ?? null, ['gallery', 'gallery_2', 'gallery_3', 'gallery_4'], true)) {
                $sourceImages = data_get($block, 'data.images', []);
                $translatedImages = data_get($translated[$index], 'data.images', []);
                $sourceImages = is_array($sourceImages) ? $sourceImages : [];
                $translatedImages = is_array($translatedImages) ? $translatedImages : [];

                if (count($sourceImages) !== count($translatedImages)) {
                    throw new RuntimeException('Переводчик изменил количество фотографий в галерее. Перевод не применён.');
                }

                foreach ($sourceImages as $imageIndex => $sourceImage) {
                    $translatedImages[$imageIndex] = is_array($translatedImages[$imageIndex] ?? null)
                        ? $translatedImages[$imageIndex]
                        : [];
                    $translatedImages[$imageIndex]['path'] = data_get($sourceImage, 'path');
                }

                $translated[$index]['data']['images'] = array_values($translatedImages);
            }
        }

        return array_values($translated);
    }

    /**
     * Keep RichEditor values as HTML whenever the source contains rich text.
     * A provider may still return plain text despite the prompt; in that case
     * convert blank lines to separate paragraphs and single line breaks to
     * <br> so hydration cannot silently flatten the editor content.
     *
     * @param  array<int, array<string, mixed>>  $source
     * @param  array<int, array<string, mixed>>  $translated
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeRichTextBlocks(array $source, array $translated): array
    {
        foreach ($source as $index => $sourceBlock) {
            $sourceText = data_get($sourceBlock, 'data.text');
            $translatedText = data_get($translated[$index] ?? [], 'data.text');

            if (blank($translatedText) || (! is_array($translated[$index] ?? null))) {
                continue;
            }

            $translated[$index]['data']['text'] = $this->normalizeRichTextValue(
                $sourceText,
                $translatedText,
            );
        }

        return array_values($translated);
    }

    protected function normalizeRichTextValue(mixed $source, mixed $translated): string
    {
        if (is_array($translated)) {
            return RichText::toHtml($translated);
        }

        $translated = trim((string) $translated);

        if ($translated === '') {
            return '';
        }

        if (preg_match('/<\/?(?:p|strong|em|a|ul|ol|li|blockquote|br)\b/i', $translated) === 1) {
            return $translated;
        }

        $sourceHtml = RichText::toHtml($source);
        $sourceHasRichStructure = is_array($source)
            || preg_match('/<\/?(?:p|strong|em|a|ul|ol|li|blockquote|br)\b/i', $sourceHtml) === 1
            || preg_match('/(?:\r\n|\r|\n){2,}/', (string) $source) === 1;

        return $sourceHasRichStructure
            ? RichText::plainTextToHtml($translated)
            : $translated;
    }

    /**
     * @param  array<string, mixed>|null  $overrides
     * @return array{api_key: ?string, model: string, base_url: string, app_name: string}
     */
    protected function settings(?array $overrides = null): array
    {
        return [
            'api_key' => $overrides['api_key'] ?? SiteSetting::getEncrypted('ai.openrouter_api_key'),
            'model' => trim((string) ($overrides['model'] ?? SiteSetting::getValue('ai.openrouter_model', ''))),
            'base_url' => (string) ($overrides['base_url'] ?? SiteSetting::getValue('ai.openrouter_base_url', 'https://openrouter.ai/api/v1')),
            'app_name' => (string) ($overrides['app_name'] ?? SiteSetting::getValue('ai.app_name', 'Green Energy Hub')),
        ];
    }
}
