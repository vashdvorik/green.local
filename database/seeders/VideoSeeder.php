<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $videos = [
            [
                'youtube_id' => 'x0AIDgyz6Qg',
                'youtube_url' => 'https://youtu.be/x0AIDgyz6Qg',
                'event_date' => '2026-08-01',
                'position' => 1,
                'title' => [
                    'ru' => 'Энергоэффективность на практике',
                    'ro' => 'Eficiența energetică în practică',
                    'en' => 'Energy efficiency in practice',
                ],
                'description' => [
                    'ru' => 'Практические подходы к рациональному использованию энергии.',
                    'ro' => 'Abordări practice pentru utilizarea rațională a energiei.',
                    'en' => 'Practical approaches to using energy more efficiently.',
                ],
            ],
            [
                'youtube_id' => 'VP8GqtLYr38',
                'youtube_url' => 'https://youtu.be/VP8GqtLYr38',
                'event_date' => '2026-08-05',
                'position' => 2,
                'title' => [
                    'ru' => 'Обучение и работа с оборудованием',
                    'ro' => 'Instruire și lucru cu echipamente',
                    'en' => 'Training and equipment in practice',
                ],
                'description' => [
                    'ru' => 'Знания и измерения, которые помогают принимать точные решения.',
                    'ro' => 'Cunoștințe și măsurători pentru decizii mai precise.',
                    'en' => 'Knowledge and measurements for more precise decisions.',
                ],
            ],
            [
                'youtube_id' => 'mgS3xvbKI3g',
                'youtube_url' => 'https://youtu.be/mgS3xvbKI3g',
                'event_date' => '2026-08-10',
                'position' => 3,
                'title' => [
                    'ru' => 'Обмен опытом и развитие сообщества',
                    'ro' => 'Schimb de experiență și dezvoltarea comunității',
                    'en' => 'Experience exchange and community',
                ],
                'description' => [
                    'ru' => 'Истории, встречи и решения для устойчивого энергетического будущего.',
                    'ro' => 'Povești, întâlniri și soluții pentru un viitor energetic durabil.',
                    'en' => 'Stories, meetings and solutions for a resilient energy future.',
                ],
            ],
        ];

        foreach ($videos as $video) {
            Video::firstOrCreate(
                ['youtube_id' => $video['youtube_id']],
                $video,
            );
        }
    }
}
