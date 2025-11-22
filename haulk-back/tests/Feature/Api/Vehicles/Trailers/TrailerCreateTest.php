<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Enums\Format\DateTimeEnum;
use App\Models\Files\File;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Api\Vehicles\VehicleCreateTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TrailerCreateTest extends VehicleCreateTest
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'trailers.store';

    protected string $tableName = Trailer::TABLE_NAME;

    protected array $requestData = [];

    protected function getRequestData(): array
    {
        if (empty($this->requestData)) {
            $this->requestData = [
                'vin' => 'DFDFDF3234234',
                'unit_number' => 'df763ws',
                'make' => 'Audi',
                'model' => 'A3',
                'year' => '2020',
                'license_plate' => 'SD34343',
                'temporary_plate' => 'WEF-745',
                'notes' => 'test notes',
                'owner_id' => $this->ownerFactory()->id,
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
        $unitNumber =  'df763';
        $make = 'Audi';
        $model = 'A3';
        $year = '2020';
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
                    'unit_number' => 'asdfgh45gbjuu',
                    'make' => $make,
                    'model' => $model,
                    'year' => $year,
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
        factory(Trailer::class)->create(['vin' => $vin]);

        $requestData = [
            'vin' => $vin,
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
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
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $this->loginAsPermittedUser();
        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'driver_id' => $driver->id,
            'driver_attach_at' => $date->format(DateTimeEnum::DATE_TIME_FRONT),
            'registration_number' => '12dfsfsdf-df',
            'registration_date' => now()->format('m/d/Y'),
            'registration_expiration_date' => now()->format('m/d/Y'),
            'registration_file' => UploadedFile::fake()->image('image1.jpg'),
            'inspection_date' => now()->format('m/d/Y'),
            'inspection_expiration_date' => now()->format('m/d/Y'),
           'inspection_file' => UploadedFile::fake()->create('doc.pdf'),
        ];

        $data = $this->postJson(route($this->routeName), $requestData)
            ->assertCreated();

        $this->assertDatabaseHas($this->tableName, [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'driver_id' => $driver->id,
            'driver_attach_at' => $date->format(DateTimeEnum::DATE_TIME_BACK),
            'registration_number' => '12dfsfsdf-df',
            'registration_date' => now()->format('Y-m-d'),
            'registration_expiration_date' => now()->format('Y-m-d'),
            'inspection_date' => now()->format('Y-m-d'),
            'inspection_expiration_date' => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $data['data']['id'],
                'collection_name' => Vehicle::REGISTRATION_DOCUMENT_NAME,
            ]
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $data['data']['id'],
                'collection_name' => Vehicle::INSPECTION_DOCUMENT_NAME,
            ]
        );
    }

    public function test_it_create_with_gps_device(): void
    {
        $user = $this->loginAsPermittedUser();
        $company = $user->getCompany();
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
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'gps_device_id' => $device->id,
        ];

        $data = $this->postJson(route($this->routeName), $requestData)
            ->assertCreated();

        $this->assertDatabaseHas($this->tableName, $requestData);
    }
}
