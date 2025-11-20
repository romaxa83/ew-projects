<?php

namespace Database\Factories\Page;

use App\Models\Page\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageTranslationFactory extends Factory
{
    protected $model = PageTranslation::class;

    public function definition(): array
    {
        return [
            'lang' => \App::getLocale(),
            'text' => $this->faker->paragraph,
            'name' => $this->faker->sentence,
        ];
    }
}

