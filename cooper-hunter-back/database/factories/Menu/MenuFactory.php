<?php

namespace Database\Factories\Menu;

use App\Enums\Menu\MenuBlockEnum;
use App\Enums\Menu\MenuPositionEnum;
use App\Models\About\Page;
use App\Models\Localization\Language;
use App\Models\Menu\Menu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Menu[]|Menu create(array $attributes = [])
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'position' => MenuPositionEnum::HEADER,
            'block' => MenuBlockEnum::OTHER,
            'active' => true,
        ];
    }

    public function configure(): MenuFactory
    {
        return $this->afterCreating(
            fn(Menu $menu) => $menu
                ->translations()
                ->createMany(
                    languages()
                        ->map(
                            fn(Language $language) => [
                                'title' => $this->faker->text,
                                'language' => $language->slug,
                            ]
                        )
                        ->values()
                        ->toArray()
                )
        );
    }
}
