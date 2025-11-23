<?php

namespace WezomCms\Pages\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Pages\Models\Page;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition()
    {
        $name = $this->faker->realText(25);

        $data = array_fill_keys(array_keys(app('locales')), [
            'published' => true,
            'name' => $name,
            'h1' => $name,
            'title' => $name,
            'text' => $this->faker->realText(500),
        ]);

        $data["type"] = array_rand(Page::list());

        return $data;
    }
}
