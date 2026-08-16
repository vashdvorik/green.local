<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoNewsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'slug' => 'green-energy-hub-practical-solutions',
                'published_at' => Carbon::create(2026, 8, 12, 10, 0),
                'title' => [
                    'ru' => 'Green Energy Hub: от понимания к практическим решениям',
                    'ro' => 'Green Energy Hub: de la înțelegere la soluții practice',
                    'en' => 'Green Energy Hub: from understanding to practical solutions',
                ],
                'excerpt' => [
                    'ru' => 'Публикация-заглушка для будущего материала о работе хаба, его подходе и результатах.',
                    'ro' => 'Placeholder pentru un material despre activitatea hubului, abordare și rezultate.',
                    'en' => 'A placeholder for a future story about the Hub, its approach and results.',
                ],
                'content' => [
                    'ru' => self::paragraph('Публикация-заглушка для будущего материала о работе хаба, его подходе и результатах.'),
                    'ro' => self::paragraph('Placeholder pentru un material despre activitatea hubului, abordare și rezultate.'),
                    'en' => self::paragraph('A placeholder for a future story about the Hub, its approach and results.'),
                ],
            ],
            [
                'slug' => 'practical-measurements-and-equipment',
                'published_at' => Carbon::create(2026, 8, 10, 10, 0),
                'title' => [
                    'ru' => 'Практика измерений и работа с оборудованием',
                    'ro' => 'Măsurători practice și lucru cu echipamente',
                    'en' => 'Practical measurement and equipment work',
                ],
                'excerpt' => [
                    'ru' => 'Краткий анонс будущей новости или отчёта о практическом занятии.',
                    'ro' => 'Un scurt rezumat al unei știri sau al unui raport despre o sesiune practică.',
                    'en' => 'A short excerpt for a future news item or practical session report.',
                ],
                'content' => [
                    'ru' => self::paragraph('Краткий анонс будущей новости или отчёта о практическом занятии.'),
                    'ro' => self::paragraph('Un scurt rezumat al unei știri sau al unui raport despre o sesiune practică.'),
                    'en' => self::paragraph('A short excerpt for a future news item or practical session report.'),
                ],
            ],
            [
                'slug' => 'experience-exchange-and-expert-community',
                'published_at' => Carbon::create(2026, 8, 8, 10, 0),
                'title' => [
                    'ru' => 'Обмен опытом и развитие экспертного сообщества',
                    'ro' => 'Schimb de experiență și dezvoltarea comunității',
                    'en' => 'Experience exchange and a growing community',
                ],
                'excerpt' => [
                    'ru' => 'Место для анонса встречи, интервью, видеоматериала или партнёрского события.',
                    'ro' => 'Loc pentru un anunț despre o întâlnire, interviu, video sau eveniment partener.',
                    'en' => 'A space for an event, interview, video or partner announcement.',
                ],
                'content' => [
                    'ru' => self::paragraph('Место для анонса встречи, интервью, видеоматериала или партнёрского события.'),
                    'ro' => self::paragraph('Loc pentru un anunț despre o întâlnire, interviu, video sau eveniment partener.'),
                    'en' => self::paragraph('A space for an event, interview, video or partner announcement.'),
                ],
            ],
        ];

        foreach ($items as $item) {
            News::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    ...$item,
                    'status' => 'published',
                    'cover_image' => null,
                ],
            );
        }
    }

    private static function paragraph(string $text): array
    {
        return [[
            'type' => 'paragraph',
            'data' => ['text' => $text],
        ]];
    }
}
