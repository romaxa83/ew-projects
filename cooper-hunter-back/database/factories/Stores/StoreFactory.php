<?php

namespace Database\Factories\Stores;

use App\Models\Stores\Store;
use App\Models\Stores\StoreCategory;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Store[]|Store create(array $attributes = [])
 */
class StoreFactory extends BaseFactory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'store_category_id' => StoreCategory::factory(),
            'link' => $this->faker->imageUrl,
        ];
    }
}
