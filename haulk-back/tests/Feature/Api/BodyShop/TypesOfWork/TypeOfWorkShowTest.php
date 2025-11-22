<?php

namespace Api\BodyShop\TypesOfWork;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use App\Models\BodyShop\TypesOfWork\TypeOfWorkInventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TypeOfWorkShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_for_unauthorized_users()
    {
        $typeOfWork = factory(TypeOfWork::class)->create();

        $this->getJson(route('body-shop.types-of-work.show', $typeOfWork))->assertUnauthorized();
    }

    public function test_it_not_show_for_not_permitted_users()
    {
        $typeOfWork = factory(TypeOfWork::class)->create();

        $this->loginAsCarrierAdmin();

        $this->getJson(route('body-shop.types-of-work.show', $typeOfWork))
            ->assertForbidden();
    }

    public function test_it_show_for_permitted_users()
    {
        $typeOfWork = factory(TypeOfWork::class)->create();
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 2,
        ]);

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.types-of-work.show', $typeOfWork))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'duration',
                'hourly_rate',
                'inventories' => [
                    '*' => [
                        'id',
                        'name',
                        'stock_number',
                        'price',
                        'quantity',
                    ],
                ],
                'estimated_amount',
            ]]);

        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.types-of-work.show', $typeOfWork))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'duration',
                'hourly_rate',
                'inventories' => [
                    '*' => [
                        'id',
                        'name',
                        'stock_number',
                        'price',
                        'quantity',
                    ],
                ],
                'estimated_amount',
            ]]);
    }
}
