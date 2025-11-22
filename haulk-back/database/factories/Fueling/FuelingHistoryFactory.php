<?php

namespace Database\Factories\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelingHistoryStatusEnum;
use App\Enums\Fueling\FuelingSourceEnum;
use App\Enums\Fueling\FuelingStatusEnum;
use App\Models\Fueling\Fueling;
use App\Models\Fueling\FuelingHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method FuelingHistory|FuelingHistory[]|Collection create($attributes = [], ?Model $parent = null)
 */
class FuelingHistoryFactory extends Factory
{
    protected $model = FuelingHistory::class;

    public function definition(): array
    {
        return [
            'carrier_id' => !empty($data['carrier_id']) ? $data['carrier_id'] : 1,
            'status' => FuelingHistoryStatusEnum::IN_QUEUE,
            'provider' => FuelCardProviderEnum::QUIKQ,
            'path_file' => 'file',
            'original_name' => 'file',
            'total' => 500,
            'counts_success' => 500,
        ];
    }
}
