<?php

namespace Database\Factories\Stores;

use App\Models\Stores\Distributor;
use App\Models\Stores\DistributorTranslation;
use Database\Factories\BaseTranslationFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|DistributorTranslation[]|DistributorTranslation create(array $attributes = [])
 */
class DistributorTranslationFactory extends BaseTranslationFactory
{
    protected $model = DistributorTranslation::class;

    public function definition(): array
    {
        return [
            'row_id' => Distributor::factory(),
            'title' => $this->faker->company,
        ];
    }
}
