<?php

namespace Database\Factories\Catalog\Categories;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\Models\Catalog\Categories\Category;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Category|Category[]|Collection create(array $attrs = [])
 */
class CategoryFactory extends BaseFactory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'enable_seer' => false,
            'guid' => $this->faker->uuid,
            'parent_id' => null,
            'slug' => $this->faker->slug,
            'owner_type' => ProductOwnerType::COOPER()
        ];
    }

    public function disabled(): static
    {
        return $this->state(
            [
                'active' => false
            ]
        );
    }

    public function enableSeer(): static
    {
        return $this->state(
            [
                'enable_seer' => true
            ]
        );
    }

    public function withParent(Category|CategoryFactory|null $factory = null): self
    {
        if (!$factory) {
            $factory = Category::factory();
        }

        return $this->state(
            [
                'parent_id' => $factory
            ]
        );
    }
}

