<?php

namespace Api\BodyShop\Inventories\Units;

use App\Models\BodyShop\Inventories\Unit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class UnitCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $this->postJson(route('body-shop.inventory-units.store'), [])->assertUnauthorized();
    }

    public function test_it_create()
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'name' => 'Name Test',
            'accept_decimals' => true,
        ];

        $this->assertDatabaseMissing(Unit::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.inventory-units.store'), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Unit::TABLE_NAME, $formRequest);
    }

    public function test_it_validation_messages()
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->postJson(route('body-shop.inventory-units.store'), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => ['parameter' => 'name'],
                            'title' => 'The Name field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                        [
                            'source' => ['parameter' => 'accept_decimals'],
                            'title' => 'The Accept Decimals field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ]
                    ],
                ]
            );
    }
}
