<?php

namespace Feature\Http\Api\V1\Orders\BS\Action;

use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWork;
use App\Models\Orders\BS\TypeOfWorkInventory;
use App\Models\Vehicles\Trailer;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkInventoryBuilder;
use Tests\Builders\Orders\BS\PaymentBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class RestoreWithEditingTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected UserBuilder $userBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected TransactionBuilder $transactionBuilder;
    protected OrderTypeOfWorkBuilder $orderTypeOfWorkBuilder;
    protected OrderTypeOfWorkInventoryBuilder $orderTypeOfWorkInventoryBuilder;
    protected PaymentBuilder $paymentBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->paymentBuilder = resolve(PaymentBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->transactionBuilder = resolve(TransactionBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderTypeOfWorkBuilder = resolve(OrderTypeOfWorkBuilder::class);
        $this->orderTypeOfWorkInventoryBuilder = resolve(OrderTypeOfWorkInventoryBuilder::class);

        $truck = $this->truckBuilder->create();
        $user = $this->userBuilder->asMechanic()->create();
        $this->data = [
            'truck_id' => $truck->id,
            'discount' => 10.9,
            'tax_inventory' => 5,
            'tax_labor' => 5.4,
            'implementation_date' => CarbonImmutable::now()->format('Y-m-d H:i'),
            'mechanic_id' => $user->id,
            'notes' => 'some text',
            'due_date' => CarbonImmutable::now()->format('Y-m-d H:i'),
            'types_of_work' => [
                [
                    'name' => 'type_or_work_1_update',
                    'duration' => '2:30',
                    'hourly_rate' => 30,
                ]
            ],
        ];
    }

    /** @test */
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $now = CarbonImmutable::now();

        $data = $this->data;
        $data['implementation_date'] = $now->subDays(2)->format('Y-m-d H:i');
        $data['due_date'] = $now->subDays(1)->format('Y-m-d H:i');

        $this->assertNotEquals($model->vehicle_id, data_get($data, 'truck_id'));
        $this->assertNotEquals($model->discount, data_get($data, 'discount'));
        $this->assertNotEquals($model->tax_inventory, data_get($data, 'tax_inventory'));
        $this->assertNotEquals($model->tax_labor, data_get($data, 'tax_labor'));
        $this->assertNotEquals($model->implementation_date, data_get($data, 'implementation_date'));
        $this->assertNotEquals($model->mechanic_id, data_get($data, 'mechanic_id'));
        $this->assertNotEquals($model->notes, data_get($data, 'notes'));
        $this->assertNotEquals($model->due_date, data_get($data, 'due_date'));
        $this->assertEmpty($model->typesOfWork);

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'order_number' => $model->order_number,
                    'vehicle' => [
                        'id' => data_get($data, 'truck_id')
                    ],
                    'mechanic' => [
                        'id' => data_get($data, 'mechanic_id')
                    ],
                    'discount' => data_get($data, 'discount'),
                    'tax_labor' => data_get($data, 'tax_labor'),
                    'tax_inventory' => data_get($data, 'tax_inventory'),
                    'implementation_date' => $now->subDays(2)->format('Y-m-d H:i'),
                    'due_date' => $now->subDays(1)->format('Y-m-d'),
                    'notes' => data_get($data, 'notes'),
                ],
            ])
        ;

        $model->refresh();

        $this->assertNull($model->status_before_deleting);
        $this->assertNull($model->deleted_at);
    }

    /** @test */
    public function success_update_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $trailer = $this->trailerBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $now = CarbonImmutable::now();

        $data = $this->data;
        $data['implementation_date'] = $now->subDays(2)->format('Y-m-d H:i');
        $data['due_date'] = $now->subDays(1)->format('Y-m-d H:i');
        $data['truck_id'] = null;
        $data['trailer_id'] = $trailer->id;

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'vehicle' => [
                        'id' => $trailer->id,
                        'vehicle_form' => Trailer::MORPH_NAME,
                    ]
                ],
            ])
        ;

        $model->refresh();

        $history = $model->histories;

        $this->assertCount(2, $history);


        $historyUpdate = $model->histories()->where('msg', 'history.order.common.updated')->first();
        $this->assertEquals($historyUpdate->user_id, $user->id);
        $this->assertEquals($historyUpdate->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);
        $this->assertNotEmpty($historyUpdate->details);

        $historyRestore = $model->histories()->where('msg', 'history.order.bs.restored')->first();
        $this->assertEquals($historyRestore->user_id, $user->id);
        $this->assertEquals($historyRestore->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);
        $this->assertEmpty($historyRestore->details);
    }

    /** @test */
    public function success_update_with_attachments()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->attachments(
            UploadedFile::fake()->image('img_11.png')
        )
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $now = CarbonImmutable::now();

        $data = $this->data;
        $data[Order::ATTACHMENT_FIELD_NAME] = [
            UploadedFile::fake()->image('img_1.png'),
            UploadedFile::fake()->image('img_2.png'),
        ];

        $this->assertCount(1, $model->getAttachments());

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(3, 'data.attachments')
        ;

        $model->refresh();

        $history = $model->histories[0];

        $img_1 = $model->media()->where('name', 'img_1')->first();
        $this->assertEquals($history->details['attachments.'.$img_1->id.'.name'], [
            'old' => null,
            'new' => $img_1->name,
            'type' => 'added',
        ]);
        $img_2 = $model->media()->where('name', 'img_2')->first();
        $this->assertEquals($history->details['attachments.'.$img_2->id.'.name'], [
            'old' => null,
            'new' => $img_2->name,
            'type' => 'added',
        ]);

        $history_1 = $model->histories[1];
        $this->assertEquals($history_1->msg, 'history.order.bs.restored');
    }

    /** @test */
    public function success_update_work_types_create_or_update()
    {
        $this->loginUserAsSuperAdmin();

        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)->inventory($inventory_1)->create();
        $work_inventory_2 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)->inventory($inventory_2)->create();

        $work_2 = $this->orderTypeOfWorkBuilder->order($model)->create();

        $data = $this->data;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
        ];
        // update
        $data['types_of_work'][1] = [
            'id' => $work_2->id,
            'name' => $work_2->name . 'update',
            'duration' => $work_1->duration,
            'hourly_rate' => (int)$work_2->hourly_rate + 30,
        ];
        // create
        $data['types_of_work'][2] = [
            'name' => 'new work 3',
            'duration' => "0:30",
            'hourly_rate' => 10,
        ];

        $this->assertCount(2, $model->typesOfWork);
        $this->assertCount(2, $model->typesOfWork[0]->inventories);
        $this->assertNotEquals($model->typesOfWork[1]->name, $data['types_of_work'][1]['name']);
        $this->assertNotEquals($model->typesOfWork[1]->hourly_rate, $data['types_of_work'][1]['hourly_rate']);

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'order_number' => $model->order_number,
                    'types_of_work' => [
                        [
                            'id' => $work_1->id,
                            'name' => $work_1->name,
                            'duration' => $data['types_of_work'][0]['duration'],
                            'hourly_rate' => $data['types_of_work'][0]['hourly_rate'],
                        ],
                        [
                            'id' => $work_2->id,
                            'name' => $data['types_of_work'][1]['name'],
                            'duration' => $data['types_of_work'][1]['duration'],
                            'hourly_rate' => $data['types_of_work'][1]['hourly_rate']
                        ],
                        [
                            'name' => $data['types_of_work'][2]['name'],
                            'duration' => $data['types_of_work'][2]['duration'],
                            'hourly_rate' => $data['types_of_work'][2]['hourly_rate']
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(0, 'data.types_of_work.0.inventories')
            ->assertJsonCount(0, 'data.types_of_work.1.inventories')
            ->assertJsonCount(0, 'data.types_of_work.2.inventories')
            ->assertJsonCount(3, 'data.types_of_work')
        ;
    }

    /** @test */
    public function success_update_work_types_create_and_add_to_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();

        $data = $this->data;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
        ];
        // create
        $data['types_of_work'][1] = [
            'name' => 'new work 3',
            'duration' => "0:30",
            'hourly_rate' => 10,
            'save_to_the_list' => true
        ];
        $data['types_of_work'][2] = [
            'name' => 'new work 4',
            'duration' => "1:30",
            'hourly_rate' => 20,
            'save_to_the_list' => true
        ];

        $this->assertCount(1, $model->typesOfWork);
        $this->assertCount(0, $model->typesOfWork[0]->inventories);

        $this->assertFalse(\App\Models\TypeOfWorks\TypeOfWork::query()->where('name', $data['types_of_work'][0]['name'])->exists());
        $this->assertFalse(\App\Models\TypeOfWorks\TypeOfWork::query()->where('name', $data['types_of_work'][1]['name'])->exists());
        $this->assertFalse(\App\Models\TypeOfWorks\TypeOfWork::query()->where('name', $data['types_of_work'][2]['name'])->exists());

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'order_number' => $model->order_number,
                    'types_of_work' => [
                        [
                            'id' => $work_1->id,
                            'name' => $work_1->name,
                            'duration' => $data['types_of_work'][0]['duration'],
                            'hourly_rate' => $data['types_of_work'][0]['hourly_rate'],
                        ],
                        [
                            'name' => $data['types_of_work'][1]['name'],
                            'duration' => $data['types_of_work'][1]['duration'],
                            'hourly_rate' => $data['types_of_work'][1]['hourly_rate']
                        ],
                        [
                            'name' => $data['types_of_work'][2]['name'],
                            'duration' => $data['types_of_work'][2]['duration'],
                            'hourly_rate' => $data['types_of_work'][2]['hourly_rate']
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(0, 'data.types_of_work.0.inventories')
            ->assertJsonCount(0, 'data.types_of_work.1.inventories')
            ->assertJsonCount(0, 'data.types_of_work.2.inventories')
            ->assertJsonCount(3, 'data.types_of_work')
        ;

        $this->assertFalse(\App\Models\TypeOfWorks\TypeOfWork::query()->where('name', $data['types_of_work'][0]['name'])->exists());
        $this->assertTrue(\App\Models\TypeOfWorks\TypeOfWork::query()->where('name', $data['types_of_work'][1]['name'])->exists());
        $this->assertTrue(\App\Models\TypeOfWorks\TypeOfWork::query()->where('name', $data['types_of_work'][2]['name'])->exists());

    }

    /** @test */
    public function success_update_work_types_deleted_reduceReservedInOrder()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity(20)->create();
        $inventory_2 = $this->inventoryBuilder->quantity(20)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $transaction_1 = $this->transactionBuilder
            ->order($model)
            ->inventory($inventory_1)
            ->qty(10)
            ->is_reserve(true)
            ->create();
        $transactionId_1 = $transaction_1->id;
        $transaction_2 = $this->transactionBuilder
            ->order($model)
            ->inventory($inventory_2)
            ->qty(11)
            ->is_reserve(true)
            ->create();
        $transactionId_2 = $transaction_2->id;

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();

        $work_2 = $this->orderTypeOfWorkBuilder->order($model)->create();
        /** @var $work_inventory_1 TypeOfWorkInventory */
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_2)
            ->qty(10)
            ->inventory($inventory_1)->create();
        $work_inventory_2 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_2)
            ->qty(11)
            ->inventory($inventory_2)->create();

        $data = $this->data;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
        ];

        $this->assertCount(2, $model->typesOfWork);

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'order_number' => $model->order_number,
                    'types_of_work' => [
                        [
                            'id' => $work_1->id,
                        ],
                    ]
                ],
            ])
            ->assertJsonCount(0, 'data.types_of_work.0.inventories')
            ->assertJsonCount(1, 'data.types_of_work')
        ;

        $inventory_1->refresh();
        $inventory_2->refresh();

        $this->assertEquals($inventory_1->quantity, 20);
        $this->assertEquals($inventory_2->quantity, 20);

        $this->assertNotNull(Transaction::find($transactionId_1));
        $this->assertNotNull(Transaction::find($transactionId_2));

        $this->assertEmpty($inventory_1->histories);
        $this->assertEmpty($inventory_2->histories);
    }

    /** @test */
    public function success_update_work_types_create_or_update_inventory()
    {
        $this->loginUserAsSuperAdmin();

        $inventory_1 = $this->inventoryBuilder->quantity(20)->create();
        $inventory_2 = $this->inventoryBuilder->quantity(20)->create();
        $inventory_3 = $this->inventoryBuilder->quantity(20)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)->inventory($inventory_1)->create();
        $work_inventory_2 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)->inventory($inventory_2)->create();

        $data = $this->data;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
            'inventories' => [
                [
                    'id' => $inventory_1->id,
                    'quantity' => 9,
                ],
                [
                    'id' => $inventory_3->id,
                    'quantity' => 3,
                ]
            ]
        ];
        // create
        $data['types_of_work'][1] = [
            'name' => 'new work 3',
            'duration' => "0:30",
            'hourly_rate' => 10,
            'inventories' => [
                [
                    'id' => $inventory_2->id,
                    'quantity' => 9,
                ],
            ]
        ];

        $this->assertCount(1, $model->typesOfWork);

        $this->assertEquals(
            $model->typesOfWork[0]->inventories[0]->inventory_id,
            $data['types_of_work'][0]['inventories'][0]['id']
        );
        $this->assertNotEquals(
            $model->typesOfWork[0]->inventories[0]->quantity,
            $data['types_of_work'][0]['inventories'][0]['quantity']
        );

        $this->assertNotEquals(
            $model->typesOfWork[0]->inventories[1]->inventory_id,
            $data['types_of_work'][0]['inventories'][1]['id']
        );
        $this->assertNotEquals(
            $model->typesOfWork[0]->inventories[1]->quantity,
            $data['types_of_work'][0]['inventories'][1]['quantity']
        );

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'order_number' => $model->order_number,
                    'types_of_work' => [
                        [
                            'id' => $work_1->id,
                            'name' => $work_1->name,
                            'inventories' => [
                                [
                                    'id' => $work_inventory_1->id,
                                    'inventory_id' => $inventory_1->id,
                                    'quantity' => $data['types_of_work'][0]['inventories'][0]['quantity'],
                                    'price' => $work_inventory_1->price,
                                ],
                                [
                                    'inventory_id' => $inventory_3->id,
                                    'quantity' => $data['types_of_work'][0]['inventories'][1]['quantity'],
                                    'price' => $inventory_3->price_retail,
                                ]
                            ]
                        ],
                        [
                            'name' => $data['types_of_work'][1]['name'],
                            'inventories' => [
                                [
                                    'inventory_id' => $inventory_2->id,
                                    'quantity' => $data['types_of_work'][1]['inventories'][0]['quantity'],
                                    'price' => $inventory_2->price_retail,
                                ]
                            ],
                        ],
                    ]
                ],
            ])
            ->assertJsonCount(2, 'data.types_of_work.0.inventories')
            ->assertJsonCount(1, 'data.types_of_work.1.inventories')
            ->assertJsonCount(2, 'data.types_of_work')
        ;
    }

    /** @test */
    public function success_update_work_types_delete_inventory_reduceReservedInOrder()
    {
        $user = $this->loginUserAsSuperAdmin();
        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity(20)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $transaction = $this->transactionBuilder
            ->order($model)
            ->inventory($inventory_1)
            ->qty(10)
            ->is_reserve(true)
            ->create();
        $transactionId = $transaction->id;

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        /** @var $work_inventory_1 TypeOfWorkInventory */
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->qty(10)
            ->inventory($inventory_1)->create();

        $data = $this->data;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
        ];

        $this->assertCount(1, $model->typesOfWork);
        $this->assertCount(1, $model->typesOfWork[0]->inventories);

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'order_number' => $model->order_number,
                ],
            ])
            ->assertJsonCount(0, 'data.types_of_work.0.inventories')
            ->assertJsonCount(1, 'data.types_of_work')
        ;

        $inventory_1->refresh();

        $this->assertEquals($inventory_1->quantity, 20);
        $this->assertNotNull(Transaction::find($transactionId));

        $this->assertEmpty($inventory_1->histories);
    }

    /** @test */
    public function success_update_work_type_inventory_change_reserved_qty_for_inventory_transaction_decrease()
    {
        $user = $this->loginUserAsSuperAdmin();

        $oldQty = 5;
        $newQty = 9;
        $currentQty = 20;

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity($currentQty)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();
        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->inventory($inventory_1)
            ->qty($oldQty)
            ->order($model)
            ->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->inventory($inventory_1)
            ->qty($oldQty)
            ->create();

        $data = $this->data;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
            'inventories' => [
                [
                    'id' => $inventory_1->id,
                    'quantity' => $newQty,
                ],
            ]
        ];

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'types_of_work' => [
                        [
                            'id' => $work_1->id,
                            'name' => $work_1->name,
                            'inventories' => [
                                [
                                    'id' => $work_inventory_1->id,
                                    'inventory_id' => $inventory_1->id,
                                    'quantity' => $newQty,
                                    'price' => $work_inventory_1->price,
                                ],
                            ]
                        ],
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.types_of_work.0.inventories')
            ->assertJsonCount(1, 'data.types_of_work')
        ;

        $transaction->refresh();
        $inventory_1->refresh();

        $this->assertEquals($transaction->quantity, $oldQty);
        $this->assertEquals($inventory_1->quantity, $currentQty - $newQty);

        $history = $inventory_1->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_reserved_for_order');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_1->stock_number,
            'inventory_name' => $inventory_1->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($work_inventory_1->price, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.bs_order_show_url')),
            'order_number' => $model->order_number,
        ]);

        $this->assertEquals($history->details, [
            "quantity" => [
                'old' => $currentQty,
                'new' => $currentQty - $newQty,
                'type' => 'updated',
            ],
        ]);
    }

    /** @test */
    public function success_update_work_type_inventory_change_reserved_qty_for_inventory_transaction_increase()
    {
        $user = $this->loginUserAsSuperAdmin();

        $oldQty = 5;
        $newQty = 2;
        $currentQty = 20;
        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity($currentQty)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();
        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->inventory($inventory_1)
            ->qty($oldQty)
            ->order($model)
            ->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->inventory($inventory_1)
            ->qty($oldQty)
            ->create();

        $data = $this->data;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
            'inventories' => [
                [
                    'id' => $inventory_1->id,
                    'quantity' => $newQty,
                ],
            ]
        ];

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'types_of_work' => [
                        [
                            'id' => $work_1->id,
                            'name' => $work_1->name,
                            'inventories' => [
                                [
                                    'id' => $work_inventory_1->id,
                                    'inventory_id' => $inventory_1->id,
                                    'quantity' => $newQty,
                                    'price' => $work_inventory_1->price,
                                ],
                            ]
                        ],
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.types_of_work.0.inventories')
            ->assertJsonCount(1, 'data.types_of_work')
        ;

        $transaction->refresh();
        $inventory_1->refresh();

        $this->assertEquals($transaction->quantity, $oldQty);
        $this->assertEquals($inventory_1->quantity, $currentQty - $newQty);

        $history = $inventory_1->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_reserved_for_order');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_1->stock_number,
            'inventory_name' => $inventory_1->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($work_inventory_1->price, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.bs_order_show_url')),
            'order_number' => $model->order_number,
        ]);

        $this->assertEquals($history->details, [
            "quantity" => [
                'old' => $currentQty,
                'new' => $currentQty - $newQty,
                'type' => 'updated',
            ],
        ]);
    }

    /** @test */
    public function success_update_work_type_inventory_change_reserved_qty_for_inventory_transaction_no_change()
    {
        $user = $this->loginUserAsSuperAdmin();

        $oldQty = 5;
        $newQty = 5;
        $currentQty = 20;

        $inventory_1 = $this->inventoryBuilder->quantity($currentQty)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();
        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->inventory($inventory_1)
            ->qty($oldQty)
            ->order($model)
            ->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->inventory($inventory_1)
            ->qty($oldQty)
            ->create();

        $data = $this->data;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
            'inventories' => [
                [
                    'id' => $inventory_1->id,
                    'quantity' => $newQty,
                ],
            ]
        ];

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'types_of_work' => [
                        [
                            'id' => $work_1->id,
                            'name' => $work_1->name,
                            'inventories' => [
                                [
                                    'id' => $work_inventory_1->id,
                                    'inventory_id' => $inventory_1->id,
                                    'quantity' => $newQty,
                                    'price' => $work_inventory_1->price,
                                ],
                            ]
                        ],
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.types_of_work.0.inventories')
            ->assertJsonCount(1, 'data.types_of_work')
        ;

        $transaction->refresh();
        $inventory_1->refresh();

        $this->assertEquals($transaction->quantity, $newQty);
        $this->assertEquals($inventory_1->quantity, $currentQty - $newQty);

        $history = $inventory_1->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_reserved_for_order');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_1->stock_number,
            'inventory_name' => $inventory_1->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($work_inventory_1->price, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.bs_order_show_url')),
            'order_number' => $model->order_number,
        ]);

        $this->assertEquals($history->details, [
            "quantity" => [
                'old' => $currentQty,
                'new' => $currentQty - $newQty,
                'type' => 'updated',
            ],
        ]);
    }

    /** @test */
    public function success_update_work_type_inventory_create_reserved()
    {
        $this->loginUserAsSuperAdmin();

        $newQty = 5;
        $currentQty = 20;

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity($currentQty)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();

        $data = $this->data;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
            'inventories' => [
                [
                    'id' => $inventory_1->id,
                    'quantity' => $newQty,
                ],
            ]
        ];

        $this->assertEmpty($model->typesOfWork[0]->inventories);
        $this->assertEmpty($inventory_1->transactions);

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'types_of_work' => [
                        [
                            'id' => $work_1->id,
                            'name' => $work_1->name,
                            'inventories' => [
                                [
                                    'inventory_id' => $inventory_1->id,
                                    'quantity' => $newQty,
                                    'price' => $inventory_1->price_retail,
                                ],
                            ]
                        ],
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.types_of_work.0.inventories')
            ->assertJsonCount(1, 'data.types_of_work')
        ;

        $inventory_1->refresh();

        /** @var $transaction Transaction */
        $transaction = $inventory_1->transactions[0];
        $this->assertEquals($transaction->operation_type->value, OperationType::SOLD->value);
        $this->assertEquals($transaction->order_id, $model->id);
        $this->assertEquals($transaction->price, $inventory_1->price_retail);
        $this->assertEquals($transaction->invoice_number, $model->order_number);
        $this->assertNotNull($transaction->transaction_date);
        $this->assertTrue($transaction->is_reserve, OperationType::SOLD->value);
        $this->assertEquals($transaction->quantity, $newQty);

        $this->assertEquals($inventory_1->quantity, $currentQty - $newQty);
    }

    /** @test */
    public function success_update_inventory_need_to_update_prices()
    {
        $user = $this->loginUserAsSuperAdmin();

        $newPrice = 5;
        $currentPrice = 7.9;

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->price_retail($newPrice)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();
        /** @var $transaction Transaction */
        $transaction = $this->transactionBuilder
            ->order($model)
            ->inventory($inventory_1)
            ->price($currentPrice)
            ->create();

        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        /** @var $work_inventory_1 TypeOfWorkInventory */
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->inventory($inventory_1)
            ->price($currentPrice)
            ->create();

        $data = $this->data;
        $data['need_to_update_prices'] = true;
        // update
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
            'inventories' => [
                [
                    'id' => $inventory_1->id,
                    'quantity' => '3',
                ],
            ]
        ];

        $this->assertEquals($transaction->price, $currentPrice);
        $this->assertEquals($work_inventory_1->price, $currentPrice);

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(1, 'data.types_of_work.0.inventories')
            ->assertJsonCount(1, 'data.types_of_work')
        ;

        $transaction->refresh();
        $work_inventory_1->refresh();
        $inventory_1->refresh();

        $this->assertEquals($transaction->price, $currentPrice);
        $this->assertEquals($work_inventory_1->price, $newPrice);

        $history = $inventory_1->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_reserved_for_order');
    }

    /** @test */
    public function success_update_and_mark_not_billed()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->is_billed(true, $now)
            ->create();

        $data = $this->data;

        $this->assertTrue($model->is_billed);
        $this->assertNotNull($model->billed_at);

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'billed_at' => null,
                ],
            ])
        ;

        $model->refresh();
        $this->assertFalse($model->is_billed);

    }

    /** @test */
    public function success_update_set_amounts()
    {
        $this->loginUserAsSuperAdmin();

        $inventoryPrice = 5;
        $workInventoryQty = 3;

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->is_paid(false)
            ->create();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->price_retail($inventoryPrice)->quantity(20)->create();
        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        /** @var $work_inventory_1 TypeOfWorkInventory */
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->inventory($inventory_1)
            ->price($inventoryPrice)
            ->qty(10)
            ->create();

        $payment_1 = $this->paymentBuilder->amount(2)->order($model)->create();
        $payment_2 = $this->paymentBuilder->amount(12)->order($model)->create();

        $data = $this->data;
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
            'inventories' => [
                [
                    'id' => $inventory_1->id,
                    'quantity' => $workInventoryQty,
                ],
            ]
        ];

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->total_amount, $model->getAmount());
        $this->assertEquals($model->paid_amount, $payment_1->amount + $payment_2->amount);
        $this->assertEquals(round($model->debt_amount, 2), round($model->total_amount - $model->paid_amount, 2));
    }

    /** @test */
    public function success_update_resolve_paid_status_is_paid()
    {
        $this->loginUserAsSuperAdmin();

        $inventoryPrice = 5;
        $workInventoryQty = 3;

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->is_paid(false)
            ->create();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->price_retail($inventoryPrice)->create();
        /** @var $work_1 TypeOfWork */
        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        /** @var $work_inventory_1 TypeOfWorkInventory */
        $work_inventory_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->inventory($inventory_1)
            ->price($inventoryPrice)
            ->qty(10)
            ->create();

        $payment_1 = $this->paymentBuilder->amount(2)->order($model)->create();
        $payment_2 = $this->paymentBuilder->amount(120)->order($model)->create();

        $this->assertFalse($model->is_paid);
        $this->assertNull($model->paid_at);

        $data = $this->data;
        $data['types_of_work'][0] = [
            'id' => $work_1->id,
            'name' => $work_1->name,
            'duration' => $work_1->duration,
            'hourly_rate' => $work_1->hourly_rate,
            'inventories' => [
                [
                    'id' => $inventory_1->id,
                    'quantity' => $workInventoryQty,
                ],
            ]
        ];

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;

        $model->refresh();

        $this->assertTrue($model->is_paid);
        $this->assertNotNull($model->paid_at);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $data['mechanic_id'] = null;

        $res = $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.mechanic_id')]),
            'mechanic_id'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $data = $this->data;

        $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['implementation_date', null, 'validation.required', ['attribute' => 'validation.attributes.implementation_date']],
            ['due_date', null, 'validation.required', ['attribute' => 'validation.attributes.due_date']],
            ['mechanic_id', null, 'validation.required', ['attribute' => 'validation.attributes.mechanic_id']],
            ['mechanic_id', 99999, 'validation.exists', ['attribute' => 'validation.attributes.mechanic_id']],
            ['types_of_work', null, 'validation.required', ['attribute' => 'validation.attributes.types_of_work']],
        ];
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $res = $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $res = $this->postJson(route('api.v1.orders.bs.restore-with-editing', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
