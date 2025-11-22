<?php

namespace Database\Factories\Stores;

use App\Models\Stores\StoreCategory;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|StoreCategory[]|StoreCategory create(array $attributes = [])
 */
class StoreCategoryFactory extends BaseFactory
{
    protected $model = StoreCategory::class;

    public function definition(): array
    {
        return [

        ];
    }
}
