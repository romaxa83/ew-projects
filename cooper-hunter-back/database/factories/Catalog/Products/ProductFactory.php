<?php

namespace Database\Factories\Catalog\Products;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\Models\Catalog\Brands\Brand;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use App\Models\Localization\Language;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @method Product|Product[]|Collection create(array $attrs = [])
 */
class ProductFactory extends BaseFactory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'title' => $title = $this->faker->sentence,
            'slug' => Str::slug($this->faker->unique->sentence),
            'title_metaphone' => makeSearchSlug($title),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'guid' => $this->faker->uuid,
            'owner_type' => ProductOwnerType::COOPER(),
            'unit_sub_type' => null
        ];
    }

    public function disabled(): static
    {
        return $this->state(['active' => false]);
    }

    public function configure(): ProductFactory
    {
        return $this->afterCreating(
            fn(Product $product) => $product->translations()
                ->createMany(
                    languages()
                        ->map(
                            fn(Language $language) => [
                                'language' => $language->slug,
                                'description' => $this->faker->realText,
                            ]
                        )
                        ->values()
                        ->toArray()
                )
        );
    }
}
