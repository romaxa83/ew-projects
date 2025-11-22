<?php

namespace Database\Factories\Vehicles\Schemas;

use App\Enums\Vehicles\VehicleFormEnum;
use App\Models\Vehicles\Schemas\SchemaAxle;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|SchemaVehicle[]|SchemaVehicle create(array $attributes = [])
 */
class SchemaVehicleFactory extends Factory
{
    protected $model = SchemaVehicle::class;

    public function definition(): array
    {
        return [
            'vehicle_form' => VehicleFormEnum::MAIN(),
            'name' => $this->faker->name
        ];
    }

    public function trailer(): self
    {
        return $this->state(
            [
                'vehicle_form' => VehicleFormEnum::TRAILER()
            ]
        );
    }

    public function configure(): self
    {
        return $this->afterCreating(
            function (SchemaVehicle $schemaVehicle)
            {
                $originalSchema = SchemaVehicle::default()
                    ->vehicleForm($schemaVehicle->vehicle_form)
                    ->with(['wheels'])
                    ->first();

                $wheels = $originalSchema
                    ->wheels
                    ->random(mt_rand(1, $originalSchema->wheels->count()))
                    ->pluck('id')
                    ->toArray();

                foreach ($originalSchema->axles as $originalAxle) {
                    /**@var SchemaAxle $axle */
                    $axle = $schemaVehicle
                        ->axles()
                        ->create(
                            [
                                'position' => $originalAxle->position,
                                'name' => $originalAxle->name,
                            ]
                        );

                    foreach ($originalAxle->wheels as $wheel) {
                        $axle
                            ->wheels()
                            ->create(
                                [
                                    'position' => $wheel->position,
                                    'name' => $wheel->name,
                                    'pos_x' => $wheel->pos_x,
                                    'pos_y' => $wheel->pos_y,
                                    'rotate' => $wheel->rotate,
                                    'use' => in_array($wheel->id, $wheels)
                                ]
                            );
                    }
                }
            }
        );
    }
}
