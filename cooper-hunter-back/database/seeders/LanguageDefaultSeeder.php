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
                'slug' => 'en',
                'name' => 'English',
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'es',
                'name' => 'Español',
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
