<?php

namespace Database\Seeders\News;

use App\Models\News\Tag;
use App\Models\News\TagTranslation;
use Illuminate\Database\Seeder;

class TagsSeeder extends Seeder
{
    public function run(): void
    {
        if (Tag::query()->exists()) {
            return;
        }

        $this->create('News', 'Noticias', '#00BEFF;');
        $this->create('Sponsorships', 'Patrocinios', '#F9A825;');
        $this->create('Exhibition', 'Exhibición', '#1EC086;');
    }

    protected function create(string $en, string $es, string $color): void
    {
        Tag::factory()
            ->has(
                TagTranslation::factory()
                    ->count(2)
                    ->sequence(
                        [
                            'title' => $en,
                            'language' => 'en',
                        ],
                        [
                            'title' => $es,
                            'language' => 'es',
                        ],
                    ),
                'translations'
            )
            ->state(
                [
                    'color' => $color
                ]
            )
            ->create();
    }
}
