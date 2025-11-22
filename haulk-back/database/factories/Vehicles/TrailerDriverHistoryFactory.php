<?php
namespace Database\Factories\Vehicles;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\TrailerDriverHistory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrailerDriverHistoryFactory extends Factory
{
    protected $model = TrailerDriverHistory::class;

    public function definition(): array
    {
        return [
            'trailer_id' => Trailer::factory(),
            'driver_id' => User::factory(),
            'assigned_at' => CarbonImmutable::now(),
            'unassigned_at' => null,
        ];
    }
}

