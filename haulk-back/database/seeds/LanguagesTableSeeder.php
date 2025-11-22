<?php


use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguagesTableSeeder extends Seeder
{
    private const LANGUAGES = [
        [
            'name' => 'English',
            'slug' => 'en',
            'default' => true
        ],
        [
            'name' => 'Russian',
            'slug' => 'ru',
            'default' => false
        ],
        [
            'name' => 'Spanish',
            'slug' => 'es',
            'default' => false
        ],
        [
            'name' => 'Ukrainian',
            'slug' => 'uk',
            'default' => false
        ],
    ];

    public function run()
    {
        foreach (self::LANGUAGES AS $language) {
            Language::updateOrCreate(
                [
                    'slug' => $language['slug']
                ],
                [
                    'name' => $language['name'],
                    'default' => $language['default']
                ]
            );
        }
    }
}
