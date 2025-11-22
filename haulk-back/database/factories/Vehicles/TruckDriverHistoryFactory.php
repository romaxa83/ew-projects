<?php
namespace Database\Factories\Vehicles;

use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\TruckDriverHistory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class TruckDriverHistoryFactory extends Factory
{
    protected $model = TruckDriverHistory::class;

    public function definition(): array
    {
        return [
            'truck_id' => Truck::factory(),
            'driver_id' => User::factory(),
            'assigned_at' => CarbonImmutable::now(),
            'unassigned_at' => null,
        ];
    }
}
