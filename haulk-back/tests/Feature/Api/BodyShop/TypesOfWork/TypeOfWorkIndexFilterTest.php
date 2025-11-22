<?php

namespace Tests\Feature\Api\BodyShop\TypesOfWork;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use App\Models\BodyShop\TypesOfWork\TypeOfWorkInventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TypeOfWorkIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsBodyShopSuperAdmin();

        $type1 = factory(TypeOfWork::class)->create([
            'name' => 'Name1',
        ]);

        $type2 = factory(TypeOfWork::class)->create([
            'name' => 'Name2',
        ]);

        $type3 = factory(TypeOfWork::class)->create([
            'name' => 'Name3',
        ]);


        $filter = ['q' => 'Name3'];
        $response = $this->getJson(route('body-shop.types-of-work.index', $filter))
            ->assertOk();

        $types = $response->json('data');
        $this->assertCount(1, $types);
        $this->assertEquals($type3->id, $types[0]['id']);
    }

    public function test_sort()
    {
        $this->loginAsBodyShopSuperAdmin();

        $type1 = factory(TypeOfWork::class)->create([
            'name' => 'Name1',
        ]);

        $type2 = factory(TypeOfWork::class)->create([
            'name' => 'Name2',
        ]);

        $type3 = factory(TypeOfWork::class)->create([
            'name' => 'Name3',
        ]);


        $params = ['order_by' => 'name', 'order_type' => 'desc'];
        $response = $this->getJson(route('body-shop.types-of-work.index', $params))
            ->assertOk();

        $categories = $response->json('data');
        $this->assertEquals($type1->id, $categories[2]['id']);
        $this->assertEquals($type2->id, $categories[1]['id']);
        $this->assertEquals($type3->id, $categories[0]['id']);
    }

    public function test_filter_by_inventory(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $type1 = factory(TypeOfWork::class)->create();
        $inventory1 = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'inventory_id' => $inventory1->id,
            'type_of_work_id' => $type1->id,
            'quantity' => 1,
        ]);
        $type2 = factory(TypeOfWork::class)->create();
        $inventory2 = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'inventory_id' => $inventory2->id,
            'type_of_work_id' => $type2->id,
            'quantity' => 1,
        ]);


        $filter = ['inventory_id' => $inventory2->id];
        $response = $this->getJson(route('body-shop.types-of-work.index', $filter))
            ->assertOk();

        $types = $response->json('data');
        $this->assertCount(1, $types);
        $this->assertEquals($type2->id, $types[0]['id']);
    }
}
