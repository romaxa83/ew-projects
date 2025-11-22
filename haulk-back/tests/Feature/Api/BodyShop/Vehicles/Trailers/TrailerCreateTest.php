<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trailers;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Feature\Api\Vehicles\VehicleCreateTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TrailerCreateTest extends VehicleCreateTest
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'body-shop.trailers.store';

    protected string $tableName = Trailer::TABLE_NAME;

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
                    'unit_number' => 'asdfghdfgertr',
                    'make' => $make,
                    'model' => $model,
                    'year' => $year,
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
}
