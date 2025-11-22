<?php


namespace Database\Seeders;


use App\Enums\Vehicles\VehicleFormEnum;
use App\Models\Vehicles\Schemas\SchemaAxle;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Schemas\SchemaWheel;
use Illuminate\Database\Seeder;

class SchemaWheelSeeder extends Seeder
{

    private const SCHEMAS = [
        [
            'name' => 'default_' . VehicleFormEnum::MAIN,
            'is_default' => true,
            'vehicle_form' => VehicleFormEnum::MAIN,
        ],
        [
            'name' => 'default_' . VehicleFormEnum::TRAILER,
            'is_default' => true,
            'vehicle_form' => VehicleFormEnum::TRAILER,
        ],
    ];

    private const AXLES = [
        [
            'position' => 1,
            'name' => 'F2',
            'vehicle_type' => VehicleFormEnum::MAIN,
            'action_if_empty' => null,
        ],
        [
            'position' => 2,
            'name' => 'F2',
            'vehicle_type' => VehicleFormEnum::MAIN,
            'action_if_empty' => null,
        ],
        [
            'position' => 3,
            'name' => 'D4',
            'vehicle_type' => VehicleFormEnum::MAIN,
        ],
        [
            'position' => 4,
            'name' => 'D4',
            'vehicle_type' => VehicleFormEnum::MAIN,
        ],
        [
            'position' => 5,
            'name' => 'S1',
            'vehicle_type' => VehicleFormEnum::MAIN,
        ],
        [
            'position' => 1,
            'name' => 'T2',
            'vehicle_type' => VehicleFormEnum::TRAILER,
        ],
        [
            'position' => 2,
            'name' => 'T2',
            'vehicle_type' => VehicleFormEnum::TRAILER,
        ],
        [
            'position' => 3,
            'name' => 'T2',
            'vehicle_type' => VehicleFormEnum::TRAILER,
        ],
        [
            'position' => 4,
            'name' => 'T2',
            'vehicle_type' => VehicleFormEnum::TRAILER,
        ],
        [
            'position' => 5,
            'name' => 'S1',
            'vehicle_type' => VehicleFormEnum::TRAILER,
        ],
    ];

    private const WHEELS = [
        [
            'position' => 1,
            'name' => '6',
            'rotate' => 0,
            'pos_x' => 165,
            'pos_y' => 67,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'F2-1',
        ],
        [
            'position' => 2,
            'name' => '1',
            'rotate' => 0,
            'pos_x' => 165,
            'pos_y' => 745,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'F2-1',
        ],
        [
            'position' => 1,
            'name' => '6a',
            'rotate' => 0,
            'pos_x' => 573,
            'pos_y' => 67,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'F2-2',
        ],
        [
            'position' => 2,
            'name' => '1a',
            'rotate' => 0,
            'pos_x' => 573,
            'pos_y' => 745,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'F2-2',
        ],
        [
            'position' => 1,
            'name' => '4',
            'rotate' => 0,
            'pos_x' => 1182,
            'pos_y' => 67,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'D4-3',
        ],
        [
            'position' => 2,
            'name' => '5',
            'rotate' => 0,
            'pos_x' => 1182,
            'pos_y' => 259,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'D4-3',
        ],
        [
            'position' => 3,
            'name' => '3',
            'rotate' => 0,
            'pos_x' => 1182,
            'pos_y' => 553,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'D4-3',
        ],
        [
            'position' => 4,
            'name' => '2',
            'rotate' => 0,
            'pos_x' => 1182,
            'pos_y' => 745,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'D4-3',
        ],
        [
            'position' => 1,
            'name' => '4a',
            'rotate' => 0,
            'pos_x' => 1566,
            'pos_y' => 67,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'D4-4',
        ],
        [
            'position' => 2,
            'name' => '5a',
            'rotate' => 0,
            'pos_x' => 1566,
            'pos_y' => 259,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'D4-4',
        ],
        [
            'position' => 3,
            'name' => '3a',
            'rotate' => 0,
            'pos_x' => 1566,
            'pos_y' => 553,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'D4-4',
        ],
        [
            'position' => 4,
            'name' => '2a',
            'rotate' => 0,
            'pos_x' => 1566,
            'pos_y' => 745,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'D4-4',
        ],
        [
            'position' => 1,
            'name' => '7',
            'rotate' => 90,
            'pos_x' => 1984,
            'pos_y' => 298,
            'vehicle_type' => VehicleFormEnum::MAIN,
            'axle' => 'S1-5',
        ],
        [
            'position' => 1,
            'name' => '13',
            'rotate' => 0,
            'pos_x' => 147,
            'pos_y' => 66,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-1',
        ],
        [
            'position' => 2,
            'name' => '13a',
            'rotate' => 0,
            'pos_x' => 147,
            'pos_y' => 258,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-1',
        ],
        [
            'position' => 3,
            'name' => '8a',
            'rotate' => 0,
            'pos_x' => 147,
            'pos_y' => 552,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-1',
        ],
        [
            'position' => 4,
            'name' => '8',
            'rotate' => 0,
            'pos_x' => 147,
            'pos_y' => 744,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-1',
        ],
        [
            'position' => 1,
            'name' => '12',
            'rotate' => 0,
            'pos_x' => 531,
            'pos_y' => 66,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-2',
        ],
        [
            'position' => 2,
            'name' => '12a',
            'rotate' => 0,
            'pos_x' => 531,
            'pos_y' => 258,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-2',
        ],
        [
            'position' => 3,
            'name' => '9a',
            'rotate' => 0,
            'pos_x' => 531,
            'pos_y' => 552,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-2',
        ],
        [
            'position' => 4,
            'name' => '9',
            'rotate' => 0,
            'pos_x' => 531,
            'pos_y' => 744,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-2',
        ],
        [
            'position' => 1,
            'name' => '11',
            'rotate' => 0,
            'pos_x' => 915,
            'pos_y' => 66,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-3',
        ],
        [
            'position' => 2,
            'name' => '11a',
            'rotate' => 0,
            'pos_x' => 915,
            'pos_y' => 258,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-3',
        ],
        [
            'position' => 3,
            'name' => '10a',
            'rotate' => 0,
            'pos_x' => 915,
            'pos_y' => 552,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-3',
        ],
        [
            'position' => 4,
            'name' => '10',
            'rotate' => 0,
            'pos_x' => 915,
            'pos_y' => 744,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-3',
        ],
        [
            'position' => 1,
            'name' => '15',
            'rotate' => 0,
            'pos_x' => 1299,
            'pos_y' => 66,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-4',
        ],
        [
            'position' => 2,
            'name' => '15a',
            'rotate' => 0,
            'pos_x' => 1299,
            'pos_y' => 258,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-4',
        ],
        [
            'position' => 3,
            'name' => '16a',
            'rotate' => 0,
            'pos_x' => 1299,
            'pos_y' => 552,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-4',
        ],
        [
            'position' => 4,
            'name' => '16',
            'rotate' => 0,
            'pos_x' => 1299,
            'pos_y' => 744,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'T2-4',
        ],
        [
            'position' => 1,
            'name' => '14',
            'rotate' => 90,
            'pos_x' => 1698,
            'pos_y' => 296,
            'vehicle_type' => VehicleFormEnum::TRAILER,
            'axle' => 'S1-5',
        ],
    ];

    private array $schemas = [];
    private array $axles = [];

    public function run(): void
    {
        $this->saveSchemas()
            ->saveAxles()
            ->saveWheels();
    }

    private function saveWheels(): void
    {
        foreach (self::WHEELS as $wheel) {
            SchemaWheel::updateOrCreate(
                [
                    'position' => $wheel['position'],
                    'schema_axle_id' => $this->axles[$wheel['axle'] . '-' . $wheel['vehicle_type']]
                ],
                [
                    'name' => $wheel['name'],
                    'position' => $wheel['position'],
                    'pos_x' => $wheel['pos_x'],
                    'pos_y' => $wheel['pos_y'],
                    'rotate' => $wheel['rotate'],
                ]
            );
        }
    }

    private function saveAxles(): self
    {
        foreach (self::AXLES as $axle) {
            $this->axles[$axle['name'] . '-' . $axle['position'] . '-' . $axle['vehicle_type']] = SchemaAxle::updateOrCreate(
                [
                    'position' => $axle['position'],
                    'schema_vehicle_id' => $this->schemas[$axle['vehicle_type']]
                ],
                [
                    'name' => $axle['name'],
                ]
            )->id;
        }

        return $this;
    }

    private function saveSchemas(): self
    {
        foreach (self::SCHEMAS as $schema) {
            $this->schemas[$schema['vehicle_form']] = SchemaVehicle::updateOrCreate(
                [
                    'name' => $schema['name'],
                    'is_default' => $schema['is_default']
                ],
                [
                    'vehicle_form' => $schema['vehicle_form']
                ]
            )->id;
        }
        return $this;
    }
}
