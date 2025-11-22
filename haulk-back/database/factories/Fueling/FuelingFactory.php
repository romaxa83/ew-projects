<?php

namespace Database\Factories\Fueling;

use App\Enums\Format\DateTimeEnum;
use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelingSourceEnum;
use App\Enums\Fueling\FuelingStatusEnum;
use App\Models\Fueling\Fueling;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Fueling|Fueling[]|Collection create($attributes = [], ?Model $parent = null)
 */
class FuelingFactory extends Factory
{
    protected $model = Fueling::class;

    public function definition(): array
    {
        return [
            'carrier_id' => !empty($data['carrier_id']) ? $data['carrier_id'] : 1,
            'card' => 55552,
            'uuid' => $this->faker->uuid,
            'transaction_date' => now()->format(DateTimeEnum::DATE_TIME_BACK),
            'timezone' => $this->faker->timezone,
            'user' => $this->faker->firstName,
            'location' => $this->faker->address,
            'state' => 'NY',
            'fees' => 50,
            'item' => 'item',
            'unit_price' => 20.5,
            'quantity' => 5,
            'amount' => 100,
            'status' => FuelingStatusEnum::DUE,
            'source' => FuelingSourceEnum::IMPORT,
            'provider' => FuelCardProviderEnum::EFS,
            'valid' => true,
        ];
    }
}
