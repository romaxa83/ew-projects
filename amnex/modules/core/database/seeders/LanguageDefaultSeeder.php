<?php

namespace Wezom\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Wezom\Core\Models\Language;

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
                'slug' => Language::UK,
                'name' => 'Українська',
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => Language::RU,
                'name' => 'русский',
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => Language::EN,
                'name' => 'English',
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
