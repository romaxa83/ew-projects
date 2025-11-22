<?php

namespace Database\Seeders;

use App\Models\Faq\Faq;
use App\Models\Faq\FaqTranslation;
use Illuminate\Database\Seeder;

class FaqDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::query()->doesntExist()) {
            Faq::factory()
                ->times(10)
                ->has(FaqTranslation::factory()->allLocales(), 'translations')
                ->create();
        }
    }
}
