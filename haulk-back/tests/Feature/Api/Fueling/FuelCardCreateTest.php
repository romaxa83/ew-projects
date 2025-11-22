<?php

namespace Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Saas\Company\Company;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class FuelCardCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $this->postJson(route('fuel-cards.store'), [])->assertUnauthorized();
    }

    public function test_it_create()
    {
        $this->loginAsCarrierSuperAdmin();

        $formRequest = [
            'card' => '55555',
            'provider' => FuelCardProviderEnum::EFS(),
            'status' => FuelCardStatusEnum::ACTIVE(),
        ];

        $this->assertDatabaseMissing(FuelCard::TABLE_NAME, $formRequest);

        $this->postJson(route('fuel-cards.store'), $formRequest)
            ->assertCreated()
            ->assertJsonStructure(['data' => [
                'id',
                'card',
                'provider',
                'status',
                'driver',
            ]]);

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $formRequest);

        $formRequest = [
            'card' => '11111',
            'provider' => FuelCardProviderEnum::EFS(),
            'status' => FuelCardStatusEnum::INACTIVE(),
        ];

        $this->assertDatabaseMissing(FuelCard::TABLE_NAME, $formRequest);

        $this->postJson(route('fuel-cards.store'), $formRequest)
            ->assertCreated()
            ->assertJsonStructure(['data' => [
                'id',
                'card',
                'provider',
                'status',
                'driver',
            ]]);

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $formRequest);
    }

    public function test_it_create_quikq()
    {
        $this->loginAsCarrierSuperAdmin();

        $formRequest = [
            'card' => '555555',
            'provider' => FuelCardProviderEnum::QUIKQ(),
            'status' => FuelCardStatusEnum::ACTIVE(),
        ];

        $this->assertDatabaseMissing(FuelCard::TABLE_NAME, $formRequest);

        $this->postJson(route('fuel-cards.store'), $formRequest)
            ->assertCreated()
            ->assertJsonStructure(['data' => [
                'id',
                'card',
                'provider',
                'status',
                'driver',
            ]]);

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $formRequest);

        $formRequest = [
            'card' => '111111',
            'provider' => FuelCardProviderEnum::QUIKQ(),
            'status' => FuelCardStatusEnum::INACTIVE(),
        ];

        $this->assertDatabaseMissing(FuelCard::TABLE_NAME, $formRequest);

        $this->postJson(route('fuel-cards.store'), $formRequest)
            ->assertCreated()
            ->assertJsonStructure(['data' => [
                'id',
                'card',
                'provider',
                'status',
                'driver',
            ]]);

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $formRequest);
    }

    public function test_it_validation_messages()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->postJson(route('fuel-cards.store'), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => ['parameter' => 'card'],
                            'title' => 'The card field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'provider'],
                            'title' => 'The provider field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'status'],
                            'title' => 'The status field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                    ],
                ]
            );
    }

    public function test_it_validation_unique_card_messages()
    {
        $this->loginAsCarrierSuperAdmin();

        $card = FuelCard::factory()->create([
            'card' => 55555,
            'provider' => FuelCardProviderEnum::EFS()
        ]);
        $formRequest = [
            'card' => '55555',
            'provider' => FuelCardProviderEnum::EFS(),
            'status' => FuelCardStatusEnum::ACTIVE(),
        ];
        $this->assertDatabaseCount(FuelCard::TABLE_NAME, 1);

        $this->postJson(route('fuel-cards.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => ['parameter' => 'card'],
                            'title' => 'The card has already been taken.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                    ],
                ]
            );
        $this->assertDatabaseCount(FuelCard::TABLE_NAME, 1);

    }
}
