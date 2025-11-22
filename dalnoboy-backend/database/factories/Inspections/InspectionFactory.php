<?php

namespace Database\Factories\Inspections;

use App\Enums\Inspections\InspectionModerationEntityEnum;
use App\Enums\Inspections\InspectionModerationFieldEnum;
use App\Enums\Vehicles\VehicleFormEnum;
use App\Models\Dictionaries\InspectionReason;
use App\Models\Drivers\Driver;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Testing\File;

/**
 * @method Collection|Inspection[]|Inspection create(array $attributes = [])
 */
class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    private bool $unableToSign = false;

    public function definition(): array
    {
        $vehicle = Vehicle::factory()
            ->create();

        return [
            'inspector_id' => User::factory(),
            'vehicle_id' => $vehicle->id,
            'driver_id' => Driver::factory(),
            'inspection_reason_id' => InspectionReason::factory(),
            'inspection_reason_description' => $this->faker->text,
            'unable_to_sign' => $this->unableToSign,
            'odo' => $vehicle->odo,
            'is_moderated' => true,
            'moderation_fields' => [],
        ];
    }

    public function forInspector(User $inspector): self
    {
        return $this->state(
            [
                'inspector_id' => $inspector->id,
                'branch_id' => $inspector->branch->id
            ]
        );
    }

    public function linkTrailer(?InspectionFactory $trailer = null): self
    {
        return $this->has(
            $trailer ?? Inspection::factory()
                ->forTrailer(),
            'trailer'
        );
    }

    public function forTrailer(): self
    {
        $vehicle = Vehicle::factory()
            ->trailer()
            ->create();

        return $this->state(
            [
                'vehicle_id' => $vehicle->id,
                'odo' => $vehicle->odo
            ]
        );
    }

    public function notModerated(): self
    {
        return $this->state(
            [
                'is_moderated' => false,
            ]
        );
    }

    public function withModeratedFields(): self
    {
        return $this->state(
            [
                'moderation_fields' => [
                    [
                        'entity' => InspectionModerationEntityEnum::VEHICLE,
                        'field' => InspectionModerationFieldEnum::ODO,
                        'message' => 'inspections.validation_messages.odo.too_small'
                    ],
                    [
                        'entity' => InspectionModerationEntityEnum::VEHICLE,
                        'field' => InspectionModerationFieldEnum::PHOTO_SIGN,
                        'message' => 'inspections.validation_messages.photos.sign.is_required'
                    ],
                ]
            ]
        );
    }

    public function withOutSignature(): self
    {
        $this->unableToSign = true;

        return $this->state(
            [
                'unable_to_sign' => $this->unableToSign
            ]
        );
    }

    public function configure(): self
    {
        return $this->afterCreating(
            function (Inspection $inspection)
            {
                $wheels = $inspection->vehicle->schemaVehicle->wheels;

                foreach ($wheels as $wheel) {
                    $tire = InspectionTire::factory(
                        [
                            'inspection_id' => $inspection->id,
                            'schema_wheel_id' => $wheel->id
                        ]
                    );

                    if ($this->faker->boolean) {
                        $tire = $tire
                            ->withProblems()
                            ->withRecommendations();
                    }

                    $tire->create();
                }

                $inspection
                    ->addMedia(File::create(Inspection::MC_VEHICLE . '.jpeg'))
                    ->toMediaCollection(Inspection::MC_VEHICLE);
                $inspection
                    ->addMedia(File::create(Inspection::MC_STATE_NUMBER . '.jpeg'))
                    ->toMediaCollection(Inspection::MC_STATE_NUMBER);
                $inspection
                    ->addMedia(File::create(Inspection::MC_DATA_SHEET_1 . '.jpeg'))
                    ->toMediaCollection(Inspection::MC_DATA_SHEET_1);
                $inspection
                    ->addMedia(File::create(Inspection::MC_DATA_SHEET_2 . '.jpeg'))
                    ->toMediaCollection(Inspection::MC_DATA_SHEET_2);

                if (!$this->unableToSign) {
                    $inspection
                        ->addMedia(File::create(Inspection::MC_SIGN . '.jpeg'))
                        ->toMediaCollection(Inspection::MC_SIGN);
                }

                if ($inspection->vehicle->form->is(VehicleFormEnum::MAIN)) {
                    $inspection
                        ->addMedia(File::create(Inspection::MC_ODO . '.jpeg'))
                        ->toMediaCollection(Inspection::MC_ODO);
                }
            }
        );
    }
}
