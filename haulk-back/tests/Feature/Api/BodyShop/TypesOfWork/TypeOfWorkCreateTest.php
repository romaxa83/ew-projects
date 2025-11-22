<?php

namespace Api\BodyShop\TypesOfWork;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use App\Models\BodyShop\TypesOfWork\TypeOfWorkInventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class TypeOfWorkCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $this->postJson(route('body-shop.types-of-work.store'), [])
            ->assertUnauthorized();
    }

    public function test_it_create()
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'name' => 'Name Test',
            'hourly_rate' => 10.5,
            'duration' => '5:30',
        ];

        $inventory1 = factory(Inventory::class)->create();
        $inventory2 = factory(Inventory::class)->create();

        $inventories = [
            'inventories' => [
                ['id' => $inventory1->id, 'quantity' => 3],
                ['id' => $inventory2->id, 'quantity' => 1],
            ],
        ];

        $this->assertDatabaseMissing(TypeOfWork::TABLE_NAME, $formRequest);
        $this->assertDatabaseMissing(TypeOfWorkInventory::TABLE_NAME, ['inventory_id' => $inventory1->id, 'quantity' => 3]);
        $this->assertDatabaseMissing(TypeOfWorkInventory::TABLE_NAME, ['inventory_id' => $inventory2->id, 'quantity' => 1]);

        $data = $this->postJson(route('body-shop.types-of-work.store'), $formRequest + $inventories)
            ->assertCreated();

        $createdId = $data['data']['id'];

        $this->assertDatabaseHas(TypeOfWork::TABLE_NAME, $formRequest);
        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'inventory_id' => $inventory1->id,
            'quantity' => 3,
            'type_of_work_id' => $createdId,
        ]);
        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'inventory_id' => $inventory2->id,
            'quantity' => 1,
            'type_of_work_id' => $createdId,
        ]);
    }

    public function test_it_validation_messages()
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'hourly_rate' => 10.5,
            'duration' => '5:30',
        ];

        $this->postJson(route('body-shop.types-of-work.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => ['parameter' => 'name'],
                            'title' => 'The Name field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ]
                    ],
                ]
            );
    }

    public function test_quantity_validation()
    {
        $this->loginAsBodyShopSuperAdmin();

        $inventory = factory(Inventory::class)->create();
        $formRequest = [
            'name' => 'Name Test',
            'hourly_rate' => 10.5,
            'duration' => '5:30',
            'inventories' => [
                ['id' => $inventory->id, 'quantity' => 3],
            ],
        ];

        $this->postJson(route('body-shop.types-of-work.store'), $formRequest)
            ->assertCreated();

        $formRequest['inventories'][0]['quantity'] = 10.25;

        $this->postJson(route('body-shop.types-of-work.store'), $formRequest)
            ->assertCreated();

        $inventory->unit->accept_decimals = false;
        $inventory->unit->save();

        $this->postJson(route('body-shop.types-of-work.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['inventories'][0]['quantity'] = 10;
        $this->postJson(route('body-shop.types-of-work.store'), $formRequest)
            ->assertCreated();

        $formRequest['inventories'][0]['quantity'] = 10.00;
        $this->postJson(route('body-shop.types-of-work.store'), $formRequest)
            ->assertCreated();
    }

    public function test_duration_validation(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $inventory = factory(Inventory::class)->create();
        $formRequest = [
            'name' => 'Name Test',
            'hourly_rate' => 10.5,
            'duration' => '01:error',
            'inventories' => [
                ['id' => $inventory->id, 'quantity' => 3],
            ],
        ];

        $this->postJson(route('body-shop.types-of-work.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
