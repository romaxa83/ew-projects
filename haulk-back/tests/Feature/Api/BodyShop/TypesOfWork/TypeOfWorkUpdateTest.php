<?php

namespace Api\BodyShop\TypesOfWork;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use App\Models\BodyShop\TypesOfWork\TypeOfWorkInventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TypeOfWorkUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_update_for_unauthorized_users()
    {
        $typeOfWork = factory(TypeOfWork::class)->create();

        $this->putJson(route('body-shop.types-of-work.update', $typeOfWork))
            ->assertUnauthorized();
    }

    public function test_it_update_by_bs_super_admin()
    {
        $typeOfWork = factory(TypeOfWork::class)->create();
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 2,
        ]);

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
        $this->assertDatabaseMissing(TypeOfWorkInventory::TABLE_NAME, [
            'inventory_id' => $inventory1->id,
            'quantity' => 3,
            'type_of_work_id' => $typeOfWork->id,
        ]);
        $this->assertDatabaseMissing(TypeOfWorkInventory::TABLE_NAME, [
            'inventory_id' => $inventory2->id,
            'quantity' => 1,
            'type_of_work_id' => $typeOfWork->id,
        ]);

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.types-of-work.update', $typeOfWork), $formRequest + $inventories)
            ->assertOk();

        $this->assertDatabaseHas(TypeOfWork::TABLE_NAME, $formRequest);
        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'inventory_id' => $inventory1->id,
            'quantity' => 3,
            'type_of_work_id' => $typeOfWork->id,
        ]);
        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'inventory_id' => $inventory2->id,
            'quantity' => 1,
            'type_of_work_id' => $typeOfWork->id,
        ]);
        $this->assertDatabaseMissing(TypeOfWorkInventory::TABLE_NAME, [
            'inventory_id' => $inventory->id,
            'quantity' => 2,
            'type_of_work_id' => $typeOfWork->id,
        ]);
    }
}
