<?php

namespace Database\Seeders;

use App\Models\Localization\Language;

class LanguageSeeder extends BaseSeeder
{
    public function run(): void
    {
        Language::insertOrIgnore($this->data());
    }

    protected function data(): array
    {
        return [
            [
                'slug' => 'ru',
                'locale' => 'ru_RU',
                'name' => 'Русский',
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'uk',
                'locale' => 'uk_UA',
                'name' => 'Українська',
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
//            [
//                'slug' => 'en',
//                'name' => 'English',
//                'default' => false,
//                'created_at' => now(),
//                'updated_at' => now(),
//            ],
        ];
    }
}

