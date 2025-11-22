<?php

namespace Tests\Feature\Api\Vehicles\Trucks;

use App\Enums\Format\DateTimeEnum;
use App\Models\Files\File;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Api\Vehicles\VehicleCreateTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TruckCreateTest extends VehicleCreateTest
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'trucks.store';

    protected string $tableName = Truck::TABLE_NAME;

    protected array $requestData = [];

    protected function getRequestData(): array
    {
        if (empty($this->requestData)) {
            $this->requestData = [
                'vin' => 'DFDFDF3234234',
                'unit_number' => 'df763',
                'make' => 'Audi',
                'model' => 'A3',
                'year' => '2020',
                'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
                'license_plate' => 'SD34343',
                'temporary_plate' => 'WEF-745',
                'notes' => 'test notes',
                'owner_id' => $this->driverOwnerFactory()->id,
                'driver_id' => $this->driverFactory()->id,
                'color' => 'red',
                'driver_attach_at' => now()->format(DateTimeEnum::DATE_TIME_FRONT)
            ];
        }

        return $this->requestData;
    }

    public function formSubmitDataProvider(): array
    {
        $vin = 'DFDFDF3234234';
        $unitNumber =  'df76356';
        $make = 'Audi';
        $model = 'A3';
        $year = '2020';
        $type = 'Cupe';
        $licensePlate = 'SD34343';
        $temporaryPlate = 'WEF-745';
        $notes = 'test notes';

        return [
            [
                [
                    'vin' => null,
                    'unit_number' => $unitNumber,
                    'make' => $make,
                    'model' => $model,
                    'year' => $year,
                    'type' => $type,
                    'license_plate' => $licensePlate,
                    'temporary_plate' => $temporaryPlate,
                    'notes' => $notes,
                ],
                [
                    [
                        'source' => ['parameter' => 'vin'],
                        'title' => 'The VIN field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'vin' => $vin,
                    'unit_number' => null,
                    'make' => $make,
                    'model' => $model,
                    'year' => $year,
                    'type' => $type,
                    'license_plate' => $licensePlate,
                    'temporary_plate' => $temporaryPlate,
                    'notes' => $notes,
                ],
                [
                    [
                        'source' => ['parameter' => 'unit_number'],
                        'title' => 'The Unit Number field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'vin' => $vin,
                    'unit_number' => $unitNumber,
                    'make' => null,
                    'model' => $model,
                    'year' => $year,
                    'type' => $type,
                    'license_plate' => $licensePlate,
                    'temporary_plate' => $temporaryPlate,
                    'notes' => $notes,
                ],
                [
                    [
                        'source' => ['parameter' => 'make'],
                        'title' => 'The Make field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'vin' => $vin,
                    'unit_number' => $unitNumber,
                    'make' => $make,
                    'model' => null,
                    'year' => $year,
                    'type' => $type,
                    'license_plate' => $licensePlate,
                    'temporary_plate' => $temporaryPlate,
                    'notes' => $notes,
                ],
                [
                    [
                        'source' => ['parameter' => 'model'],
                        'title' => 'The Model field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'vin' => $vin,
                    'unit_number' => $unitNumber,
                    'make' => $make,
                    'model' => $model,
                    'year' => null,
                    'type' => $type,
                    'license_plate' => $licensePlate,
                    'temporary_plate' => $temporaryPlate,
                    'notes' => $notes,
                ],
                [
                    [
                        'source' => ['parameter' => 'year'],
                        'title' => 'The Year field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'vin' => $vin,
                    'unit_number' => $unitNumber,
                    'make' => $make,
                    'model' => $model,
                    'year' => $year,
                    'type' => $type,
                    'license_plate' => null,
                    'temporary_plate' => null,
                    'notes' => $notes,
                ],
                [
                    [
                        'source' => ['parameter' => 'temporary_plate'],
                        'title' => 'The Temporary Plate field is required when License Plate is not present.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'vin' => $vin,
                    'unit_number' => 'asdfgh45ret',
                    'make' => $make,
                    'model' => $model,
                    'year' => $year,
                    'type' => $type,
                    'license_plate' => $licensePlate,
                    'temporary_plate' => $temporaryPlate,
                    'notes' => $notes,
                ],
                [
                    [
                        'source' => ['parameter' => 'unit_number'],
                        'title' => 'The Unit Number may not be greater than 10 characters.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
        ];
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    public function test_it_create_without_temporary_plate(): void
    {
        $this->loginAsPermittedUser();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $requestData = [
            'vin' => 'DFDFDF3234234',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $this->driverOwnerFactory()->id,
            'driver_id' => $this->driverFactory()->id,
            'driver_attach_at' => $date->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->assertDatabaseMissing($this->tableName, $requestData);

        $this->postJson(route($this->routeName), $requestData)
            ->assertCreated();

        $this->assertDatabaseHas($this->tableName, $requestData);
    }

    public function test_it_create_with_not_unique_vin(): void
    {
        $this->loginAsPermittedUser();

        $vin = 'DFDFDF3234234';
        factory(Truck::class)->create(['vin' => $vin]);

        $requestData = [
            'vin' => $vin,
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $this->driverOwnerFactory()->id,
        ];

        $this->postJson(route($this->routeName), $requestData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_create_with_inspection_and_registration(): void
    {
        $this->loginAsPermittedUser();
        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $requestData = [
            'vin' => 'GHSJHSD6565',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'registration_number' => '12dfsfsdf-df',
            'registration_date' => now()->format('m/d/Y'),
            'registration_expiration_date' => now()->format('m/d/Y'),
            'registration_file' => UploadedFile::fake()->image('image1.jpg'),
            'inspection_date' => now()->format('m/d/Y'),
            'inspection_expiration_date' => now()->format('m/d/Y'),
            'inspection_file' => UploadedFile::fake()->create('doc.pdf'),
            'driver_id' => $this->driverFactory()->id,
            'driver_attach_at' => $date->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $data = $this->postJson(route($this->routeName), $requestData)
            ->assertCreated();

        $this->assertDatabaseHas($this->tableName, [
            'vin' => 'GHSJHSD6565',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'registration_number' => '12dfsfsdf-df',
            'registration_date' => now()->format('Y-m-d'),
            'registration_expiration_date' => now()->format('Y-m-d'),
            'inspection_date' => now()->format('Y-m-d'),
            'inspection_expiration_date' => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Truck::class,
                'model_id' => $data['data']['id'],
                'collection_name' => Vehicle::REGISTRATION_DOCUMENT_NAME,
            ]
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Truck::class,
                'model_id' => $data['data']['id'],
                'collection_name' => Vehicle::INSPECTION_DOCUMENT_NAME,
            ]
        );
    }

    public function test_it_create_with_gps_device(): void
    {
        $user = $this->loginAsPermittedUser();
        $company = $user->getCompany();
        $company->gps_enabled = true;
        $company->save();
        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();
        $device = Device::factory(['company_id' => $company->id])->create();

        $requestData = [
            'vin' => 'GHSJHSD6565',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'gps_device_id' => $device->id,
        ];

        $this->postJson(route($this->routeName), $requestData)
            ->assertCreated();

        $this->assertDatabaseHas($this->tableName, $requestData);
    }
}
