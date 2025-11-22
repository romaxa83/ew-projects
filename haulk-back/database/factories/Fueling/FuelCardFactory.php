<?php

namespace Database\Factories\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method FuelCard|FuelCard[]|Collection create($attributes = [], ?Model $parent = null)
 */
class FuelCardFactory extends Factory
{
    protected $model = FuelCard::class;

    public function definition(): array
    {
        return [
            'carrier_id' => !empty($data['carrier_id']) ? $data['carrier_id'] : 1,
            'card' => 55552,
            'provider' => FuelCardProviderEnum::EFS,
            'active' => true,
            'status' => FuelCardStatusEnum::ACTIVE,
        ];
    }
}
