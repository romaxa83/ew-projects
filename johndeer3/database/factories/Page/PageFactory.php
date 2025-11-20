<?php

namespace Database\Factories\Page;

use App\Models\Page\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'alias' => $this->faker->unique()->word,
            'active' => true,
        ];
    }
}

