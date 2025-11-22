<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trucks;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Vehicles\VehicleCreateTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TruckCreateTest extends VehicleCreateTest
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'body-shop.trucks.store';

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
                'notes' => 'test notes',
                'owner_id' => (factory(VehicleOwner::class)->create())->id,
                'color' => 'red',
            ];
        }

        return $this->requestData;
    }

    protected function getComparingDBData(): array
    {
        $data = parent::getComparingDBData();

        if (isset($data['owner_id'])) {
            $data['customer_id'] = $data['owner_id'];
            unset($data['owner_id']);
        }

        return $data;
    }

    public function formSubmitDataProvider(): array
    {
        $vin = 'DFDFDF3234234';
        $unitNumber =  'df76334';
        $make = 'Audi';
        $model = 'A3';
        $year = '2020';
        $type = 'Cupe';
        $licensePlate = 'SD34343';
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
                    'notes' => $notes,
                ],
                [
                    [
                        'source' => ['parameter' => 'license_plate'],
                        'title' => 'The License Plate field is required.',
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                    ],
                ]
            ],
            [
                [
                    'vin' => $vin,
                    'unit_number' => 'asdfghdfgdfg',
                    'make' => $make,
                    'model' => $model,
                    'year' => $year,
                    'type' => $type,
                    'license_plate' => $licensePlate,
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
        return $this->loginAsBodyShopSuperAdmin();
    }

    public function test_it_create(): void
    {
        $this->loginAsPermittedUser();
        $ownerId = (factory(VehicleOwner::class)->create())->id;

        $data = [
            'vin' => '5YJSA1DG9DFP14705',
            'unit_number' => 'AD5F',
            'make' => 'TESL',
            'model' => 'Mode',
            'year' => '2013',
            'type' => 11,
            'license_plate' => 'license-1',
        ];

        $tags = [
            'tags' => [
                (Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER]))->id
            ],
        ];

        $this->assertDatabaseMissing($this->tableName, $data + ['customer_id' => $ownerId]);

        $this->postJson(route($this->routeName), $data + $tags + ['owner_id' => $ownerId])
            ->assertCreated();

        $this->assertDatabaseHas($this->tableName, $data + ['customer_id' => $ownerId]);
    }
}
