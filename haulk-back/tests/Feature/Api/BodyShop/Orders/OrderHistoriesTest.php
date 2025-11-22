<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Payment;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\UserFactoryHelper;

class OrderHistoriesTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_show_history_filter(): void
    {
        $user = $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();

        $this->stepTwoSaveNotChanges($order);

        $this->stepThreeSaveOneChange($order);

        $params = ['order_id' => $order->id, 'user_id' => $user->id];
        $this->getJson(route('body-shop.order.histories-detailed', $params))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $user = $this->loginAsBodyShopSuperAdmin();
        $this->stepFourSaveTwoChange($order);
        $params = ['order_id' => $order->id, 'user_id' => $user->id];
        $this->getJson(route('body-shop.order.histories-detailed', $params))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_show_history_of_order_changes(): void
    {
        $this->loginAsBodyShopAdmin();

        $order = $this->createOrder();

        $this->stepOneEmptyHistories($order);
        $this->stepTwoSaveNotChanges($order);
        $this->stepThreeSaveOneChange($order);
        $this->stepFourSaveTwoChange($order);
    }

    private function createOrder(): Order
    {
        $mechanic = $this->bsMechanicFactory();
        $order = factory(Order::class)->create(['mechanic_id' => $mechanic->id, 'discount' => 10]);

        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);
        $inventory = factory(Inventory::class)->create(['quantity' => 100]);
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
        ]);
        $order->refresh();
        $order->setAmounts();

        return $order;
    }

    private function stepOneEmptyHistories(Order $order): void
    {
        //// проверяем наличие изменений по заказу = 0
        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    private function stepTwoSaveNotChanges(Order $order): void
    {
        $typeOfWork = $order->typesOfWork[0];
        /// имитируем нажатие кнопки Сохранить но поля не были изменены
       $this->postJson(
            route('body-shop.orders.update', $order),
            [
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
                        'id' => $typeOfWork->id,
                        'name' => $typeOfWork->name,
                        'hourly_rate' => $typeOfWork->hourly_rate,
                        'duration' =>$typeOfWork->duration,
                        'inventories' => [
                            ['id' => $typeOfWork->inventories[0]->inventory_id, 'quantity' => $typeOfWork->inventories[0]->quantity],
                            ['id' => $typeOfWork->inventories[1]->inventory_id, 'quantity' => $typeOfWork->inventories[1]->quantity],
                        ],
                    ],
                ],
            ]
        )->assertOk();

        // должно быть одна запись но без измененных полей
        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    private function stepThreeSaveOneChange(Order $order): void
    {
        /// изменяем одно поле discount и сохраняем,
        $typeOfWork = $order->typesOfWork[0];
        $this->postJson(
            route('body-shop.orders.update', $order),
            [
                'truck_id' => $order->truck_id,
                'discount' => $order->discount + 10,
                'tax_inventory' => $order->tax_inventory,
                'tax_labor' => $order->tax_labor,
                'implementation_date' => $order->implementation_date->format('Y-m-d H:i'),
                'mechanic_id' => $order->mechanic_id,
                'notes' => $order->notes,
                'due_date' => $order->due_date->format('Y-m-d H:i'),
                'types_of_work' => [
                    [
                        'id' => $typeOfWork->id,
                        'name' => $typeOfWork->name,
                        'hourly_rate' => $typeOfWork->hourly_rate,
                        'duration' =>$typeOfWork->duration,
                        'inventories' => [
                            ['id' => $typeOfWork->inventories[0]->inventory_id, 'quantity' => $typeOfWork->inventories[0]->quantity],
                            ['id' => $typeOfWork->inventories[1]->inventory_id, 'quantity' => $typeOfWork->inventories[1]->quantity],
                        ],
                    ],
                ],
            ]
        )->assertOk();


        // должно быть две запись и одна только с измененным полями discount
        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories')
            ->assertJsonFragment(
                [
                    'discount' => [
                        'new' => (string) ($order->discount + 10),
                        'old' => (string) $order->discount,
                        'type' => 'updated'
                    ],
                ]
            );
    }

    private function stepFourSaveTwoChange(Order $order): void
    {
        $order->refresh();
        $typeOfWork = $order->typesOfWork[0];
        // change fields due_date, types_of_work.name
        $this->postJson(
            route('body-shop.orders.update', $order),
            [
                'truck_id' => $order->truck_id,
                'discount' => $order->discount,
                'tax_inventory' => $order->tax_inventory,
                'tax_labor' => $order->tax_labor,
                'implementation_date' => $order->implementation_date->format('Y-m-d H:i'),
                'mechanic_id' => $order->mechanic_id,
                'notes' => $order->notes,
                'due_date' => (clone $order->due_date)->addDay()->format('Y-m-d H:i'),
                'types_of_work' => [
                    [
                        'id' => $typeOfWork->id,
                        'name' => 'test2name',
                        'hourly_rate' => $typeOfWork->hourly_rate,
                        'duration' =>$typeOfWork->duration,
                        'inventories' => [
                            ['id' => $typeOfWork->inventories[0]->inventory_id, 'quantity' => $typeOfWork->inventories[0]->quantity],
                            ['id' => $typeOfWork->inventories[1]->inventory_id, 'quantity' => $typeOfWork->inventories[1]->quantity],
                        ],
                    ],
                ],
            ]
        )->assertOk();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(2, 'data.0.histories')
            ->assertJsonFragment(
                [
                    'histories' => [
                        'due_date' => [
                            'old' => $order->due_date->format('Y-m-d H:i:s'),
                            'new' => $order->due_date->addDay()->format('Y-m-d H:i:s'),
                            'type' => 'updated'
                        ],
                        'typesOfWork.' .$typeOfWork->id . '.name' => [
                            'old' => $typeOfWork->name,
                            'new' => 'test2name',
                            'type' => 'updated'
                        ]
                    ]
                ]
            );
    }

    public function testTypesOfWorkHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();
        $typeOfWork = $order->typesOfWork[0];

        // add new type of work
        $this->postJson(
            route('body-shop.orders.update', $order),
            [
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
                        'id' => $typeOfWork->id,
                        'name' => $typeOfWork->name,
                        'hourly_rate' => $typeOfWork->hourly_rate,
                        'duration' =>$typeOfWork->duration,
                        'inventories' => [
                            ['id' => $typeOfWork->inventories[0]->inventory_id, 'quantity' => $typeOfWork->inventories[0]->quantity],
                            ['id' => $typeOfWork->inventories[1]->inventory_id, 'quantity' => $typeOfWork->inventories[1]->quantity],
                        ],
                    ],
                    [
                        'name' => 'Type 2',
                        'hourly_rate' => 10,
                        'duration' =>'2:15',
                        'inventories' => [],
                    ],
                ],
            ]
        )->assertOk();

        $order->refresh();
        $newTypeOfWork = $order->typesOfWork[1];
        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(3, 'data.0.histories')
            ->assertJsonFragment(
                [
                    'histories' => [
                        'typesOfWork.' . $newTypeOfWork->id . '.name' => [
                            'old' => null,
                            'new' => 'Type 2',
                            'type' => 'added'
                        ],
                        'typesOfWork.' . $newTypeOfWork->id . '.hourly_rate' => [
                            'old' => null,
                            'new' => '10',
                            'type' => 'added'
                        ],
                        'typesOfWork.' . $newTypeOfWork->id . '.duration' => [
                            'old' => null,
                            'new' => '2:15',
                            'type' => 'added'
                        ],
                    ]
                ]
            );

        // remove type of work
        $this->postJson(
            route('body-shop.orders.update', $order),
            [
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
                        'id' => $typeOfWork->id,
                        'name' => $typeOfWork->name,
                        'hourly_rate' => $typeOfWork->hourly_rate,
                        'duration' =>$typeOfWork->duration,
                        'inventories' => [
                            ['id' => $typeOfWork->inventories[0]->inventory_id, 'quantity' => $typeOfWork->inventories[0]->quantity],
                            ['id' => $typeOfWork->inventories[1]->inventory_id, 'quantity' => $typeOfWork->inventories[1]->quantity],
                        ],
                    ],
                ],
            ]
        )->assertOk();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(3, 'data.0.histories')
            ->assertJsonFragment(
                [
                    'histories' => [
                        'typesOfWork.' . $newTypeOfWork->id . '.name' => [
                            'old' => 'Type 2',
                            'new' => null,
                            'type' => 'removed'
                        ],
                        'typesOfWork.' . $newTypeOfWork->id . '.hourly_rate' => [
                            'old' => '10',
                            'new' => null,
                            'type' => 'removed'
                        ],
                        'typesOfWork.' . $newTypeOfWork->id . '.duration' => [
                            'old' => '2:15',
                            'new' => null,
                            'type' => 'removed'
                        ],
                    ]
                ]
            );
    }

    public function testTypesOfWorkInventoryHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();
        $typeOfWork = $order->typesOfWork[0];
        $newInventory = factory(Inventory::class)->create(['quantity' => 100]);

        // change 1 inventory, add 1 new inventory, delete 1 existed inventory
        $this->postJson(
            route('body-shop.orders.update', $order),
            [
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
                        'id' => $typeOfWork->id,
                        'name' => $typeOfWork->name,
                        'hourly_rate' => $typeOfWork->hourly_rate,
                        'duration' =>$typeOfWork->duration,
                        'inventories' => [
                            ['id' => $typeOfWork->inventories[0]->inventory_id, 'quantity' => $typeOfWork->inventories[0]->quantity + 1],
                            ['id' => $newInventory->id, 'quantity' => 10],
                        ],
                    ],
                ],
            ]
        )->assertOk();

        $changedInventory = $typeOfWork->inventories[0];
        $deletedInventory = $typeOfWork->inventories[1];
        $order->refresh();
        $typeOfWork->refresh();
        $addedInventory = $typeOfWork->inventories[1];
        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(7, 'data.0.histories')
            ->assertJsonFragment(
                [
                    'histories' => [
                        'typesOfWork.' . $typeOfWork->id . '.inventories.' . $changedInventory->id . '.quantity' => [
                            'old' => $changedInventory->quantity,
                            'new' => (string) ($changedInventory->quantity + 1),
                            'type' => 'updated'
                        ],
                        'typesOfWork.' . $typeOfWork->id . '.inventories.' . $addedInventory->id . '.inventory_id' => [
                            'old' => null,
                            'new' => $addedInventory->inventory->name,
                            'type' => 'added'
                        ],
                        'typesOfWork.' . $typeOfWork->id . '.inventories.' . $addedInventory->id . '.quantity' => [
                            'old' => null,
                            'new' => $addedInventory->quantity,
                            'type' => 'added'
                        ],
                        'typesOfWork.' . $typeOfWork->id . '.inventories.' . $addedInventory->id . '.price' => [
                            'old' => null,
                            'new' => $addedInventory->price,
                            'type' => 'added'
                        ],
                        'typesOfWork.' . $typeOfWork->id . '.inventories.' . $deletedInventory->id . '.inventory_id' => [
                            'old' => $deletedInventory->inventory->name,
                            'new' => null,
                            'type' => 'removed'
                        ],
                        'typesOfWork.' . $typeOfWork->id . '.inventories.' . $deletedInventory->id . '.quantity' => [
                            'old' => $deletedInventory->quantity,
                            'new' => null,
                            'type' => 'removed'
                        ],
                        'typesOfWork.' . $typeOfWork->id . '.inventories.' . $deletedInventory->id . '.price' => [
                            'old' => $deletedInventory->price,
                            'new' => null,
                            'type' => 'removed'
                        ],
                    ]
                ]
            );
    }

    public function testAttachmentsHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
        ];
        $this->postJson(route('body-shop.orders.attachments', $order), $attributes)
            ->assertOk();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');

        $order->refresh();
        $attachments = $order->getAttachments();
        $attachment = array_shift($attachments);
        $this->deleteJson(route('body-shop.orders.delete-attachments', ['order' => $order->id, 'id' => $attachment->id]))
            ->assertNoContent();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function testCommentsHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();

        $attributes = [
            'comment' => 'test comment',
        ];
        $this->postJson(route('body-shop.order-comments.store', $order), $attributes)
            ->assertCreated();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');

        $order->refresh();
        $comments = $order->comments;
        $comment = $comments[0];
        $this->deleteJson(route('body-shop.order-comments.destroy', [$order, $comment]))
            ->assertNoContent();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function testChangeStatusHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();

        $attributes = ['status' => Order::STATUS_IN_PROCESS];

        $this->putJson(route('body-shop.orders.change-status', $order), $attributes)
            ->assertOk();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function testReassignMechanicHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();

        $mechanic = $this->bsMechanicFactory();
        $attributes = ['mechanic_id' => $mechanic->id];

        $this->putJson(route('body-shop.orders.reassign-mechanic', $order), $attributes)
            ->assertOk();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'data.0.histories');
    }

    public function testDeleteOrderHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();

        $this->deleteJson(route('body-shop.orders.destroy', $order))
            ->assertNoContent();

        $this->getJson(route('body-shop.order.histories', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function testRestoreOrderHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();
        $order->delete();

        $this->putJson(route('body-shop.orders.restore', $order))
            ->assertOk();

        $this->getJson(route('body-shop.order.histories', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function testRestoreWithEditingOrderHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();
        $order->delete();

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

        $formRequest = [
            'truck_id' => $order->truck_id,
            'discount' => $order->discount,
            'tax_inventory' => $order->tax_inventory,
            'tax_labor' => $order->tax_labor,
            'implementation_date' => date('Y-m-d H:i'),
            'mechanic_id' => $order->mechanic_id,
            'notes' => $order->notes,
            'due_date' => date('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'id' => $typeOfWork1->id,
                    'name' => $typeOfWork1->name,
                    'hourly_rate' => $typeOfWork1->hourly_rate,
                    'duration' => $typeOfWork1->duration,
                    'inventories' => [
                        ['id' => $typeOfWork1Inventory1->inventory_id, 'quantity' => $typeOfWork1Inventory1->quantity + 1],
                    ],
                ]
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

        $this->postJson(route('body-shop.orders.restore-with-editing', $order), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'status' => Order::STATUS_NEW,
            ]
        );

        $this->getJson(route('body-shop.order.histories', $order))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function testAddPaymentHistoryHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();

        $attributes = [
            'amount' => $order->getAmount(),
            'payment_date' => now()->format('m/d/Y'),
            'payment_method' => Payment::PAYMENT_METHOD_VENMO,
            'notes' => 'test',
        ];

        $this->postJson(route('body-shop.orders.add-payment', $order), $attributes)
            ->assertCreated();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(4, 'data.0.histories');
    }

    public function testAddPaymentWithReferenceNumberHistoryHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();

        $attributes = [
            'amount' => $order->getAmount(),
            'payment_date' => now()->format('m/d/Y'),
            'payment_method' => Payment::PAYMENT_METHOD_MONEY_ORDER,
            'notes' => 'test',
            'reference_number' => 'eewrewrew',
        ];

        $this->postJson(route('body-shop.orders.add-payment', $order), $attributes)
            ->assertCreated();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(5, 'data.0.histories');
    }

    public function testDeletePaymentHistoryHistory(): void
    {
        $this->loginAsBodyShopAdmin();
        $order = $this->createOrder();
        $payment = factory(Payment::class)->create(['order_id' => $order->id]);

        $this->deleteJson(route('body-shop.orders.delete-payment', [$order, $payment]))
            ->assertNoContent();

        $this->getJson(route('body-shop.order.histories-detailed', $order))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(4, 'data.0.histories');
    }
}
