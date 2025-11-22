<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class OrderCreateTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_to_users_create_for_not_authorized_users(): void
    {
        $this->postJson(route('body-shop.orders.store'), [])
            ->assertUnauthorized();
    }

    public function test_it_create(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();

        $order = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
        ];

        $typeOfWork = [
            'name' => 'Name Test',
            'hourly_rate' => 10.5,
            'duration' => '5:30',
        ];

        $inventory1 = factory(Inventory::class)->create(['quantity' => 100]);
        $inventory2 = factory(Inventory::class)->create(['quantity' => 100]);

        $inventories = [
            'inventories' => [
                ['id' => $inventory1->id, 'quantity' => 3],
                ['id' => $inventory2->id, 'quantity' => 1],
            ],
        ];

        $formRequest = $order;
        $formRequest['types_of_work'] = [
            $typeOfWork + $inventories
        ];

        $this->assertDatabaseMissing(Order::TABLE_NAME, $order);
        $this->assertDatabaseMissing(TypeOfWork::TABLE_NAME, $typeOfWork);
        $this->assertDatabaseMissing(TypeOfWorkInventory::TABLE_NAME, ['inventory_id' => $inventory1->id, 'quantity' => 3]);
        $this->assertDatabaseMissing(TypeOfWorkInventory::TABLE_NAME, ['inventory_id' => $inventory2->id, 'quantity' => 1]);

        $data = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        $createdId = $data['data']['id'];
        $typeOfWorkId = $data['data']['types_of_work']['0']['id'];

        $this->assertDatabaseHas(Order::TABLE_NAME, $order);
        $this->assertDatabaseHas(TypeOfWork::TABLE_NAME, $typeOfWork + ['order_id' => $createdId]);
        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'inventory_id' => $inventory1->id,
            'quantity' => 3,
            'price' => $inventory1->price_retail,
            'type_of_work_id' => $typeOfWorkId,
        ]);
        $this->assertDatabaseHas(TypeOfWorkInventory::TABLE_NAME, [
            'inventory_id' => $inventory2->id,
            'quantity' => 1,
            'price' => $inventory2->price_retail,
            'type_of_work_id' => $typeOfWorkId,
        ]);

        $order = Order::find($createdId);
        $this->assertEmpty($order->paid_at);
        $this->assertEmpty($order->billed_at);
        $this->assertFalse($order->is_paid);
        $this->assertFalse($order->is_billed);
    }

    public function test_it_create_with_attachments(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();

        $order = [
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

        $this->assertDatabaseMissing(Order::TABLE_NAME, $order);

        $formRequest = $order + $typesOfWork +[
            Order::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg'),
                UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
            ],
        ];

        $response = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Order::TABLE_NAME, $order);

        $orderData = $response->json('data');

        $this->assertCount(3, $orderData[Order::ATTACHMENT_COLLECTION_NAME]);
    }

    public function test_quantity_validation_error(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $inventory = factory(Inventory::class)->create(['quantity' => 3]);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 4],
                    ],
                ],
            ],
        ];

        $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $inventory->quantity = 5;
        $formRequest['types_of_work'][] = [
            'name' => 'Name Test',
            'hourly_rate' => 10.5,
            'duration' => '5:30',
            'inventories' => [
                ['id' => $inventory->id, 'quantity' => 3],
            ],
        ];

        $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_quantity_validation()
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 4],
                    ],
                ],
            ],
        ];

        $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        $formRequest['types_of_work'][0]['inventories'][0]['quantity'] = 10.25;

        $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        $inventory->unit->accept_decimals = false;
        $inventory->unit->save();

        $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $formRequest['types_of_work'][0]['inventories'][0]['quantity'] = 10;
        $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();

        $formRequest['types_of_work'][0]['inventories'][0]['quantity'] = 10.00;
        $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();
    }

    public function test_it_cant_create_with_zero_amount(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();

        $order = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
        ];

        $formRequest = $order;

        $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_order_number_if_deleted_order_exist(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [],
                ],
            ],
        ];

        $data = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();
        $orderId = $data['data']['id'];
        $order = Order::find($orderId);
        $order->delete();

        $data = $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertCreated();
        $this->assertNotEquals($order->order_number, $data['data']['order_number']);
    }

    public function test_inventory_price_validation(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $truck  = factory(Truck::class)->create();
        $mechanic = $this->bsMechanicFactory();
        $inventory = factory(Inventory::class)->create(['quantity' => 100, 'price_retail' => null]);

        $formRequest = [
            'truck_id' => $truck->id,
            'discount' => 10,
            'tax_inventory' => 12,
            'tax_labor' => 8,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $mechanic->id,
            'notes' => 'test notes',
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'Name Test',
                    'hourly_rate' => 10.5,
                    'duration' => '5:30',
                    'inventories' => [
                        ['id' => $inventory->id, 'quantity' => 4],
                    ],
                ],
            ],
        ];

        $this->postJson(route('body-shop.orders.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
