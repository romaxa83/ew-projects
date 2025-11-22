<?php

namespace Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardProviderEnum;
use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class FuelCardUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $card = FuelCard::factory()->create();

        $this->putJson(route('fuel-cards.update', $card))->assertUnauthorized();
    }

    public function test_it_create()
    {
        $this->loginAsCarrierSuperAdmin();

        $card = FuelCard::factory()->create();

        $formRequest = [
            'provider' => FuelCardProviderEnum::QUIKQ(),
            'status' => FuelCardStatusEnum::INACTIVE(),
        ];

        $this->assertDatabaseMissing(FuelCard::TABLE_NAME, $formRequest);

        $this->putJson(route('fuel-cards.update', $card), $formRequest)
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'card',
                'provider',
                'status',
                'driver',
            ]]);

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $formRequest);

        $formRequest = [
            'provider' => FuelCardProviderEnum::EFS(),
            'status' => FuelCardStatusEnum::INACTIVE(),
        ];

        $this->assertDatabaseMissing(FuelCard::TABLE_NAME, $formRequest);

        $this->putJson(route('fuel-cards.update', $card), $formRequest)
            ->assertOk()
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

        $card = FuelCard::factory()->create();

        $this->putJson(route('fuel-cards.update', $card), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
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
}
