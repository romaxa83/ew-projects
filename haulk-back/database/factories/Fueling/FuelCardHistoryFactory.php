<?php

namespace Database\Factories\Fueling;

use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method FuelCardHistory|FuelCardHistory[]|Collection create($attributes = [], ?Model $parent = null)
 */
class FuelCardHistoryFactory extends Factory
{
    protected $model = FuelCardHistory::class;

    public function definition(): array
    {
        return [
            'fuel_card_id' => FuelCard::factory(),
            'user_id' => User::factory()->driver(),
            'date_assigned' => now(),
            'active' => true,
        ];
    }
}
