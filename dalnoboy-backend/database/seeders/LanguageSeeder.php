<?php

namespace Database\Seeders;

use App\Models\Localization\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    private const LANGUAGES = [
        [
            'slug' => 'ru',
            'name' => 'Русский',
            'default' => false,
        ],
        [
            'slug' => 'uk',
            'name' => 'Українська',
            'default' => true,
        ],
        [
            'slug' => 'en',
            'name' => 'English',
            'default' => false,
        ],
    ];

    public function run(): void
    {
        foreach (self::LANGUAGES as $language) {
            Language::updateOrCreate(
                [
                    'slug' => $language['slug']
                ],
                [
                    'name' => $language['name'],
                    'default' => config('app.locale') === $language['slug'],
                ]
            );
        }
    }
}
