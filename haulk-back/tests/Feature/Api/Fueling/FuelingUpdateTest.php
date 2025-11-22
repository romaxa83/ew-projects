<?php

namespace Tests\Feature\Api\Fueling;

use App\Enums\Format\DateTimeEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\Fueling;
use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class FuelingUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $fueling = Fueling::factory()->create();

        $this->putJson(route('fueling.update', $fueling))->assertUnauthorized();
    }

    public function test_it_create()
    {
        $this->loginAsCarrierSuperAdmin();

        $fueling = Fueling::factory()->create();
        $fuelCard = FuelCard::factory()->create();
        $driver = User::factory()->create();
        $driver->assignRole(User::DRIVER_ROLE);
        DriverInfo::factory()->create(
            ['driver_id' => $driver->id]
        );
        $formRequest = [
            'fuel_card_id' => $fuelCard->id,
            'transaction_date' => now()->format(DateTimeEnum::DATE_TIME_BACK),
            'timezone' => 'America/Los_Angeles',
            'user_id' => $driver->id,
            'location' => 'State NY, street Dalosa',
            'state' => 'NY',
            'fees' => 2.12,
            'item' => 'Item',
            'unit_price' => 2.12,
            'quantity' => 23.1,
        ];

        $this->assertDatabaseMissing(Fueling::TABLE_NAME, $formRequest);

        $this->putJson(route('fueling.update', $fueling), $formRequest)
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'fuelCard',
                'driver',
                'valid',
                'provider',
                'source',
                'status',
                'amount',
                'quantity',
                'unit_price',
                'item',
                'fees',
                'state',
                'location',
                'user',
                'transaction_date',
                'timezone',
            ]]);

        $this->assertDatabaseHas(Fueling::TABLE_NAME, $formRequest);

        $formRequest = [
            'fuel_card_id' => $fuelCard->id,
            'transaction_date' => now()->format(DateTimeEnum::DATE_TIME_BACK),
            'timezone' => 'America/Los_Angeles',
            'user_id' => $driver->id,
            'location' => 'State NY, street Dalos',
            'state' => 'NY',
            'fees' => 15,
            'item' => 'Items',
            'unit_price' => 10,
            'quantity' => 23.1,
        ];

        $this->assertDatabaseMissing(Fueling::TABLE_NAME, $formRequest);

        $this->putJson(route('fueling.update', $fueling), $formRequest)
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'fuelCard',
                'driver',
                'valid',
                'provider',
                'source',
                'status',
                'amount',
                'quantity',
                'unit_price',
                'item',
                'fees',
                'state',
                'location',
                'user',
                'transaction_date',
                'timezone',
            ]]);

        $this->assertDatabaseHas(Fueling::TABLE_NAME, $formRequest);
    }

    public function test_it_validation_messages()
    {
        $this->loginAsCarrierSuperAdmin();

        $fueling = Fueling::factory()->create();

        $this->putJson(route('fueling.update', $fueling), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => ['parameter' => 'fuel_card_id'],
                            'title' => 'The fuel card id field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'transaction_date'],
                            'title' => 'The transaction date field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'timezone'],
                            'title' => 'The Timezone field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'user_id'],
                            'title' => 'The user id field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'location'],
                            'title' => 'The location field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'state'],
                            'title' => 'The state field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'fees'],
                            'title' => 'The fees field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'item'],
                            'title' => 'The item field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'unit_price'],
                            'title' => 'The unit price field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'quantity'],
                            'title' => 'The Quantity field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                    ],
                ]
            );
    }
}
