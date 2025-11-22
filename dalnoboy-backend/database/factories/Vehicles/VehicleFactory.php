<?php

namespace Database\Factories\Vehicles;

use App\Enums\Vehicles\VehicleFormEnum;
use App\Models\Clients\Client;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|Vehicle[]|Vehicle create(array $attributes = [])
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $class = VehicleClass::factory()->addTypes()->create();
        $make = VehicleMake::factory()->addModels()->create();
        return [
            'state_number' => $this->faker->stateNumber,
            'vin' => $this->faker->vin,
            'is_moderated' => true,
            'form' => VehicleFormEnum::MAIN,
            'class_id' => $class->id,
            'type_id' => $class->vehicleTypes()->first()->id,
            'make_id' => $make->id,
            'model_id' => $make->vehicleModels()->first()->id,
            'client_id' => Client::factory(),
            'schema_id' => SchemaVehicle::factory(),
            'odo' => $this->faker->odo,
            'active' => true
        ];
    }

    public function trailer(): self
    {
        $class = VehicleClass::factory()
            ->trailer()
            ->addTypes()
            ->create();

        return $this->state([
            'form' => VehicleFormEnum::TRAILER,
            'class_id' => $class->id,
            'type_id' => $class->vehicleTypes()->first()->id,
            'schema_id' => SchemaVehicle::factory()->trailer(),
        ]);
    }

    public function wasNotModerated(): self
    {
        return $this->state([
            'is_moderated' => false,
        ]);
    }
}
