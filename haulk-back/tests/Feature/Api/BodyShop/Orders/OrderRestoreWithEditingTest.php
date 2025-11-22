<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderRestoreWithEditingTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_to_users_create_for_not_authorized_users(): void
    {
        $order = factory(Order::class)->create();
        $this->postJson(route('body-shop.orders.restore-with-editing', $order), [])
            ->assertUnauthorized();
    }

    public function test_it_restore(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();

        $order = factory(Order::class)->create([
            'truck_id' => $truck->id,
            'mechanic_id' => $mechanic->id,
        ]);

        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create(['quantity' => 10]);
        $typeOfWork1Inventory1= factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);
        $inventory = factory(Inventory::class)->create(['quantity' => 20]);
        $typeOfWork1Inventory2 = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
        ]);
        $inventory = factory(Inventory::class)->create(['quantity' => 10]);
        $typeOfWork1Inventory3 = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 2,
        ]);
        $inventory = factory(Inventory::class)->create(['quantity' => 10]);
        $typeOfWork1Inventory4 = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 7,
        ]);

        $typeOfWork2 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $typeOfWork3 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $typeOfWork3Inventory1 = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork3->id,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
            'price' => null,
        ]);

        $truckNew  = factory(Truck::class)->create();
        $mechanicNew = $this->bsMechanicFactory();
        $orderData = [
            'truck_id' => $truckNew->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanicNew->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
        ];

        $typeOfWork1Inventory2->inventory->price_retail = $typeOfWork1Inventory2->inventory->price_retail + 100;
        $typeOfWork1Inventory2->inventory->save();

        $typeOfWork1Inventory4->inventory->price_retail = $typeOfWork1Inventory4->inventory->price_retail + 100;
        $typeOfWork1Inventory4->inventory->save();

        $typeOfWork1Data = [
            'id' => $typeOfWork1->id,
            'name' => 'Name Test',
            'hourly_rate' => 10.5,
            'duration' => '5:30',
        ];
        $typeOfWork1DataInventories = [
            'inventories' => [
                ['id' => $typeOfWork1Inventory1->inventory_id, 'quantity' => $typeOfWork1Inventory1->quantity],
                ['id' => $typeOfWork1Inventory2->inventory_id, 'quantity' => $typeOfWork1Inventory2->quantity + 1],
                ['id' => $typeOfWork1Inventory4->inventory_id, 'quantity' => $typeOfWork1Inventory4->quantity - 1],
            ],
        ];

        $typeOfWork2Data = [
            'id' => $typeOfWork2->id,
            'name' => $typeOfWork2->name,
            'hourly_rate' => $typeOfWork2->hourly_rate,
            'duration' =>$typeOfWork2->duration,
        ];

        $typeOfWorkNew = [
            'name' => $typeOfWork2->name,
            'hourly_rate' => $typeOfWork2->hourly_rate,
            'duration' =>$typeOfWork2->duration,
        ];
        $typeOfWorkNewInventories = [
            'inventories' => [
                ['id' => $inventory->id, 'quantity' => 4],
            ],
        ];

        $formRequest = $orderData;
        $formRequest['types_of_work'] = [
            $typeOfWork1Data + $typeOfWork1DataInventories,
            $typeOfWork2Data,
            $typeOfWorkNew + $typeOfWorkNewInventories,
        ];

        $order->delete();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'status' => Order::STATUS_DELETED,
            ]
        );

        $res = $this->postJson(route('body-shop.orders.restore-with-editing', $order), $formRequest)
            /*->assertOk()*/;

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'status' => Order::STATUS_NEW,
                'deleted_at' => null,
            ]
        );
    }

    public function test_quantity_validation(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();

        $order = factory(Order::class)->create([
            'truck_id' => $truck->id,
            'mechanic_id' => $mechanic->id,
        ]);

        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);
        $typeOfWork1Inventory1= factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);
        $inventory = factory(Inventory::class)->create(['quantity' => 0]);
        $typeOfWork1Inventory2 = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
        ]);

        $typeOfWork2 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $typeOfWork2Inventory1 = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork2->id,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
            'price' => null,
        ]);

        $formRequest = [
            'truck_id' => $order->truck_id,
            'discount' => $order->discount,
            'tax_inventory' => $order->tax_inventory,
            'tax_labor' => $order->tax_labor,
            'implementation_date' => date('Y-m-d H:i', $order->implementation_date->timestamp),
            'mechanic_id' => $order->mechanic_id,
            'notes' => $order->notes,
            'due_date' => date('Y-m-d H:i', $order->due_date->timestamp),
            'types_of_work' => [
                [
                    'id' => $typeOfWork1->id,
                    'name' => $typeOfWork1->name,
                    'hourly_rate' => $typeOfWork1->hourly_rate,
                    'duration' => $typeOfWork1->duration,
                    'inventories' => [
                        ['id' => $typeOfWork1Inventory1->inventory_id, 'quantity' => 3],
                        ['id' => $typeOfWork1Inventory2->inventory_id, 'quantity' => 10],
                    ],
                ],
                [
                    'id' => $typeOfWork2->id,
                    'name' => $typeOfWork2->name,
                    'hourly_rate' => $typeOfWork2->hourly_rate,
                    'duration' => $typeOfWork2->duration,
                    'inventories' => [
                        ['id' => $typeOfWork2Inventory1->inventory_id, 'quantity' => 10],
                    ],
                ],
            ],
        ];

        $order->delete();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'status' => Order::STATUS_DELETED,
            ]
        );

        $formRequest['types_of_work'][0]['inventories'][1]['quantity'] = 11;
        $this->postJson(route('body-shop.orders.restore-with-editing', $order), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'status' => Order::STATUS_DELETED,
            ]
        );
    }
}
