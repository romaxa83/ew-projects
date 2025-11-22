<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\Payment;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_to_users_create_for_not_authorized_users(): void
    {
        $order = factory(Order::class)->create();
        $this->postJson(route('body-shop.orders.update', $order), [])
            ->assertUnauthorized();
    }

    public function test_it_update(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();

        $order = factory(Order::class)->create([
            'truck_id' => $truck->id,
            'mechanic_id' => $mechanic->id,
        ]);

        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);
        $typeOfWork1Inventory1= factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);
        $inventory = factory(Inventory::class)->create();
        $typeOfWork1Inventory2 = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
        ]);
        $inventory = factory(Inventory::class)->create();
        $typeOfWork1Inventory3 = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 2,
        ]);
        $inventory = factory(Inventory::class)->create();
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

        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Order::TABLE_NAME, $orderData);
        $this->assertDatabaseHas(TypeOfWork::TABLE_NAME, $typeOfWork1Data + ['order_id' => $order->id]);
        $this->assertDatabaseHas(TypeOfWork::TABLE_NAME, $typeOfWork2Data + ['order_id' => $order->id]);
        $this->assertDatabaseHas(TypeOfWork::TABLE_NAME, $typeOfWorkNew + ['order_id' => $order->id]);
        $this->assertDatabaseMissing(TypeOfWork::TABLE_NAME, ['id' => $typeOfWork3->id, 'order_id' => $order->id]);
        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'id' => $typeOfWork1Inventory1->id,
            'inventory_id' => $typeOfWork1Inventory1->inventory->id,
            'quantity' => $typeOfWork1Inventory1->quantity,
            'price' => $typeOfWork1Inventory1->price,
            'type_of_work_id' => $typeOfWork1->id,
        ]);
        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'id' => $typeOfWork1Inventory4->id,
            'inventory_id' => $typeOfWork1Inventory4->inventory->id,
            'quantity' => $typeOfWork1Inventory4->quantity - 1,
            'price' => $typeOfWork1Inventory4->price,
            'type_of_work_id' => $typeOfWork1->id,
        ]);
        $this->assertDatabaseMissing(TypeOfWorkInventory::TABLE_NAME, [
            'id' => $typeOfWork1Inventory3->id,
            'inventory_id' => $typeOfWork1Inventory3->inventory->id,
            'type_of_work_id' => $typeOfWork1->id,
        ]);
        $this->assertDatabaseMissing(TypeOfWorkInventory::TABLE_NAME, [
            'id' => $typeOfWork3Inventory1->id,
            'type_of_work_id' => $typeOfWork3->id,
        ]);
    }

    public function test_it_update_with_attachments(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();

        $orderData = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
        ];

        $typesOfWork = [
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                ],
            ]
        ];

        $formRequest = $orderData + $typesOfWork + [
            Order::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
                UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
            ],
        ];

        $response = $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Order::TABLE_NAME, $orderData);

        $orderDataNew = $response->json('data');

        $this->assertCount(3, $orderDataNew[Order::ATTACHMENT_COLLECTION_NAME]);
    }

    public function test_inventory_price_updating(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();

        $order = factory(Order::class)->create([
            'truck_id' => $truck->id,
            'mechanic_id' => $mechanic->id,
        ]);

        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        $typeOfWork1Inventory1= factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);
        $inventory = factory(Inventory::class)->create();
        $typeOfWork1Inventory2 = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
        ]);


        $orderData = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
        ];

        $typeOfWork1Inventory2->inventory->price_retail = $typeOfWork1Inventory2->inventory->price_retail + 100;
        $typeOfWork1Inventory2->inventory->save();

        $typeOfWork1Data = [
            'id' => $typeOfWork1->id,
            'name' => $typeOfWork1->name,
            'hourly_rate' => $typeOfWork1->hourly_rate,
            'duration' => '5:30',
        ];
        $typeOfWork1DataInventories = [
            'inventories' => [
                ['id' => $typeOfWork1Inventory1->inventory_id, 'quantity' => $typeOfWork1Inventory1->quantity],
                ['id' => $typeOfWork1Inventory2->inventory_id, 'quantity' => $typeOfWork1Inventory2->quantity],
            ],
        ];

        $formRequest = $orderData + ['need_to_update_prices' => false];
        $formRequest['types_of_work'] = [
            $typeOfWork1Data + $typeOfWork1DataInventories,
        ];

        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'id' => $typeOfWork1Inventory1->id,
            'inventory_id' => $typeOfWork1Inventory1->inventory->id,
            'quantity' => $typeOfWork1Inventory1->quantity,
            'price' => $typeOfWork1Inventory1->price,
            'type_of_work_id' => $typeOfWork1->id,
        ]);

        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'id' => $typeOfWork1Inventory2->id,
            'inventory_id' => $typeOfWork1Inventory2->inventory->id,
            'quantity' => $typeOfWork1Inventory2->quantity,
            'price' => $typeOfWork1Inventory2->price,
            'type_of_work_id' => $typeOfWork1->id,
        ]);

        $formRequest['need_to_update_prices'] = true;

        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'id' => $typeOfWork1Inventory1->id,
            'inventory_id' => $typeOfWork1Inventory1->inventory->id,
            'quantity' => $typeOfWork1Inventory1->quantity,
            'price' => $typeOfWork1Inventory1->price,
            'type_of_work_id' => $typeOfWork1->id,
        ]);

        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'id' => $typeOfWork1Inventory2->id,
            'inventory_id' => $typeOfWork1Inventory2->inventory->id,
            'quantity' => $typeOfWork1Inventory2->quantity,
            'price' => $typeOfWork1Inventory2->inventory->price_retail,
            'type_of_work_id' => $typeOfWork1->id,
        ]);
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

        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $formRequest['types_of_work'][0]['inventories'][1]['quantity'] = 11;
        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['types_of_work'][1]['inventories'][0]['quantity'] = 9;
        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $typeOfWork2Inventory1->inventory->quantity = 1;
        $typeOfWork2Inventory1->inventory->save();
        $formRequest['types_of_work'][1]['inventories'][0]['quantity'] = 10;
        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();
    }

    public function test_updating_in_finished_status(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $mechanic = $this->bsMechanicFactory();
        $order = factory(Order::class)->create([
            'mechanic_id' => $mechanic->id,
            'status' => Order::STATUS_FINISHED,
        ]);

        $orderData = [
            'truck_id' => $order->truck_id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $order->mechanic_id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
        ];

        $this->postJson(route('body-shop.orders.update', $order), $orderData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_is_billed_field_on_update(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $order = factory(Order::class)->create([
            'truck_id' => $truck->id,
            'mechanic_id' => $mechanic->id,
            'is_billed' => true,
            'billed_at' => now(),
        ]);

        $typeOfWork = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        $typeOfWorkInventory = factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork->id,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
            'price' => 10.2,
        ]);

        $formRequest = [
            'truck_id' => $order->truck_id,
            'discount' => $order->discount,
            'tax_inventory' => $order->tax_inventory,
            'tax_labor' => $order->tax_labor,
            'implementation_date' => $order->implementation_date->format('Y-m-d H:i'),
            'mechanic_id' => $order->mechanic_id,
            'notes' => $order->notes,
            'due_date' => $order->due_date->format('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => $typeOfWork->name,
                    'hourly_rate' => $typeOfWork->hourly_rate,
                    'duration' => $typeOfWork->duration,
                    'inventories' => [
                        ['id' => $typeOfWorkInventory->inventory_id, 'quantity' => 20],
                    ],
                ],
            ]
        ];

        $this->postJson(route('body-shop.orders.update', $order), $formRequest)
            ->assertOk();

        $order->refresh();
        $this->assertFalse($order->is_billed);
        $this->assertNull($order->billed_at);
    }

    public function test_updating_in_paid_status(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $mechanic = $this->bsMechanicFactory();
        $order = factory(Order::class)->create([
            'mechanic_id' => $mechanic->id,
            'is_paid' => true,
        ]);

        $orderData = [
            'truck_id' => $order->truck_id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $order->mechanic_id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
        ];

        $this->postJson(route('body-shop.orders.update', $order), $orderData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
