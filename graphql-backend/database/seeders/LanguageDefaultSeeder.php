<?php

namespace Database\Seeders;

use App\Models\Localization\Language;
use Illuminate\Database\Seeder;

class LanguageDefaultSeeder extends Seeder
{
    public function run(): void
    {
        Language::query()->insertOrIgnore($this->data());
    }

    protected function data(): array
    {
        return [
            [
                'slug' => 'ru',
                'name' => 'Русский',
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'uk',
                'name' => 'Українська',
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'en',
                'name' => 'English',
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
