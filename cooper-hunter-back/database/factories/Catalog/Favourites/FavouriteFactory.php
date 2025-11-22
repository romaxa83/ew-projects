<?php

namespace Database\Factories\Catalog\Favourites;

use App\Models\Catalog\Favourites\Favourite;
use App\Models\Catalog\Products\Product;
use App\Models\Users\User;
use Database\Factories\Catalog\Products\ProductFactory;
use Database\Factories\ForMemberTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Favourite[]|Favourite create(array $attributes = [])
 * @method Collection|Favourite[]|Favourite createQuietly(array $attributes = [])
 */
class FavouriteFactory extends Factory
{
    use ForMemberTrait;

    protected $model = Favourite::class;

    public function definition(): array
    {
        return [
            'member_id' => User::factory(),
            'member_type' => User::MORPH_NAME,
            'favorable_id' => Product::factory(),
            'favorable_type' => Product::MORPH_NAME,
            'created_at' => now(),
        ];
    }

    public function forProduct(Product|ProductFactory|null $product = null): self
    {
        if (!$product) {
            $product = Product::factory();
        }

        return $this->state(
            [
                'favorable_id' => $product,
                'favorable_type' => Product::MORPH_NAME,
            ]
        );
    }
}
