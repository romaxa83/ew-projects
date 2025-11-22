<?php

namespace Feature\Http\Api\V1\Orders\BS\Crud;

use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\BS\OrderStatus;
use App\Enums\Orders\OrderType;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\BS\Order;
use App\Models\TypeOfWorks\TypeOfWork;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected UserBuilder $userBuilder;
    protected InventoryBuilder $inventoryBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

        $truck = $this->truckBuilder->create();
        $user = $this->userBuilder->asMechanic()->create();
        $inventory = $this->inventoryBuilder->create();

        $this->data = [
            'truck_id' => $truck->id,
            'discount' => 10.9,
            'tax_inventory' => 5,
            'tax_labor' => 5.4,
            'implementation_date' => CarbonImmutable::now()->format('Y-m-d H:i'),
            'mechanic_id' => $user->id,
            'notes' => 'some text',
            'due_date' => CarbonImmutable::now()->format('Y-m-d H:i'),
            'need_to_update_prices' => false,
            'types_of_work' => [
                [
                    'name' => 'type_or_work_1',
                    'duration' => '2:30',
                    'hourly_rate' => 30,
                    'inventories' => [
                        [
                            'id' => $inventory->id,
                            'quantity' => 2,
                        ]
                    ]
                ]
            ],
        ];
    }

    /** @test */
    public function success_create()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->assertFalse(TypeOfWork::query()->where('name', $data['types_of_work'][0]['name'])->exists());

        $this->postJson(route('api.v1.orders.bs'), $data)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'vehicle' => [
                        'id',
                        'vin',
                        'unit_number',
                        'make',
                        'model',
                        'year',
                        'license_plate',
                        'temporary_plate',
                        'vehicle_form',
                        'tags' => [],
                    ],
                    'implementation_date',
                    'due_date',
                    'mechanic' => [
                        'id',
                        'first_name',
                        'last_name',
                        'phone',
                        'phone_extension',
                        'email',
                    ],
                    'customer' => [
                        'id',
                        'first_name',
                        'last_name',
                        'phone',
                        'phone_extension',
                        'email',
                    ],
                    'status_changed_at',
                    'types_of_work' => [
                        [
                            'id',
                            'name',
                            'duration',
                            'hourly_rate',
                            'inventories' => [
                                [
                                    'id',
                                    'inventory_id',
                                    'name',
                                    'stock_number',
                                    'price',
                                    'quantity',
                                    'total_amount',
                                    'total_amount',
                                    'unit' => [
                                        'id'
                                    ]
                                ]
                            ],
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'vehicle' => [
                        'id' => $data['truck_id'],
                        'vehicle_form' => Truck::MORPH_NAME
                    ],
                    'discount' => $data['discount'],
                    'tax_labor' => $data['tax_labor'],
                    'tax_inventory' => $data['tax_inventory'],
                    'notes' => $data['notes'],
                    'mechanic' => [
                        'id' => $data['mechanic_id']
                    ],
                    'status' => OrderStatus::New->value,
                    'payment_status' => null,
                    'is_prices_changed' => false,
                    'payments' => [],
                    'billed_at' => null,
                    'paid_at' => null,
                    'types_of_work' => [
                        [
                            'name' => $data['types_of_work'][0]['name'],
                            'duration' => $data['types_of_work'][0]['duration'],
                            'hourly_rate' => $data['types_of_work'][0]['hourly_rate'],
                            'inventories' => [
                                [
                                    'inventory_id' => $data['types_of_work'][0]['inventories'][0]['id'],
                                    'quantity' => $data['types_of_work'][0]['inventories'][0]['quantity']
                                ]
                            ]
                        ]
                    ]

                ],
            ])
            ->assertJsonCount(0,'data.payments')
        ;

        $this->assertFalse(TypeOfWork::query()->where('name', $data['types_of_work'][0]['name'])->exists());
    }

    /** @test */
    public function success_create_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        $implementationDate = $now->format('Y-m-d H:i');
        $dueDate = $now->format('Y-m-d');

        /** @var $mechanic User */
        $mechanic = $this->userBuilder->asMechanic()->create();

        $data = $this->data;
        $data['mechanic_id'] = $mechanic->id;
        $data['implementation_date'] = $implementationDate;
        $data['due_date'] = $dueDate;

        $id = $this->postJson(route('api.v1.orders.bs'), $data)
            ->json('data.id')
        ;

        /** @var $model Order */
        $model = Order::find($id);
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.common.created');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

        $this->assertEquals($history->details['implementation_date'], [
            'old' => null,
            'new' => $implementationDate,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['due_date'], [
            'old' => null,
            'new' => $dueDate,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['vehicle_type'], [
            'old' => null,
            'new' => 'truck',
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['vehicle_id'], [
            'old' => null,
            'new' => $data['truck_id'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['order_number'], [
            'old' => null,
            'new' => $model->order_number,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['status'], [
            'old' => null,
            'new' => OrderStatus::New->value,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['mechanic_id'], [
            'old' => null,
            'new' => $mechanic->full_name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['notes'], [
            'old' => null,
            'new' => $data['notes'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['discount'], [
            'old' => null,
            'new' => $data['discount'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['tax_inventory'], [
            'old' => null,
            'new' => $data['tax_inventory'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['tax_labor'], [
            'old' => null,
            'new' => $data['tax_labor'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['total_amount'], [
            'old' => null,
            'new' => $model->total_amount,
            'type' => 'added',
        ]);
    }

    /** @test */
    public function success_create_check_history_types_of_work()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        $data['types_of_work'] = [
            [
                'name' => 'type_or_work_1',
                'duration' => '2:30',
                'hourly_rate' => 30,
                'inventories' => [
                    [
                        'id' => $inventory_1->id,
                        'quantity' => 2,
                    ],
                    [
                        'id' => $inventory_2->id,
                        'quantity' => 3,
                    ]
                ]
            ],
            [
                'name' => 'type_or_work_2',
                'duration' => '0:30',
                'hourly_rate' => 3,
            ]
        ];

        $id = $this->postJson(route('api.v1.orders.bs'), $data)
            ->json('data.id')
        ;

        /** @var $model Order */
        $model = Order::find($id);
        $history = $model->histories[0];

        $work_1 = $model->typesOfWork()->where('name', $data['types_of_work'][0]['name'])->first();
        $work_2 = $model->typesOfWork()->where('name', $data['types_of_work'][1]['name'])->first();

        $this->assertEquals($history->msg, 'history.order.common.created');

        $this->assertEquals($history->details["typesOfWork.$work_1->id.duration"], [
            'old' => null,
            'new' => $data['types_of_work'][0]['duration'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details["typesOfWork.$work_1->id.hourly_rate"], [
            'old' => null,
            'new' => $data['types_of_work'][0]['hourly_rate'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details["typesOfWork.$work_1->id.inventories.$inventory_1->id.quantity"], [
            'old' => null,
            'new' => $data['types_of_work'][0]['inventories'][0]['quantity'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details["typesOfWork.$work_1->id.inventories.$inventory_1->id.name"], [
            'old' => null,
            'new' => $inventory_1->name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details["typesOfWork.$work_1->id.inventories.$inventory_1->id.price"], [
            'old' => null,
            'new' => $inventory_1->price_retail,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details["typesOfWork.$work_1->id.inventories.$inventory_2->id.quantity"], [
            'old' => null,
            'new' => $data['types_of_work'][0]['inventories'][1]['quantity'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details["typesOfWork.$work_1->id.inventories.$inventory_2->id.name"], [
            'old' => null,
            'new' => $inventory_2->name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details["typesOfWork.$work_1->id.inventories.$inventory_2->id.price"], [
            'old' => null,
            'new' => $inventory_2->price_retail,
            'type' => 'added',
        ]);

        $this->assertEquals($history->details["typesOfWork.$work_2->id.duration"], [
            'old' => null,
            'new' => $data['types_of_work'][1]['duration'],
            'type' => 'added',
        ]);
        $this->assertEquals($history->details["typesOfWork.$work_2->id.hourly_rate"], [
            'old' => null,
            'new' => $data['types_of_work'][1]['hourly_rate'],
            'type' => 'added',
        ]);
    }

    /** @test */
    public function success_create_check_reserve_for_order()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity(20)->create();

        $data = $this->data;
        $data['types_of_work'][0]['inventories'][0]['id'] = $inventory_1->id;

        $this->assertEmpty($inventory_1->transactions);

        $id = $this->postJson(route('api.v1.orders.bs'), $data)
            ->json('data.id')
        ;

        $inventory_1->refresh();

        $this->assertEquals($inventory_1->quantity, 20 - $data['types_of_work'][0]['inventories'][0]['quantity']);

        /** @var $transaction Transaction */
        $transaction = $inventory_1->transactions[0];

        $this->assertEquals($transaction->operation_type, OperationType::SOLD);
        $this->assertEquals($transaction->order_id, $id);
        $this->assertEquals($transaction->order_type, OrderType::BS);
        $this->assertEquals($transaction->price, $inventory_1->price_retail);
        $this->assertNotNull($transaction->transaction_date);
        $this->assertTrue($transaction->is_reserve);
        $this->assertEquals($transaction->quantity, $data['types_of_work'][0]['inventories'][0]['quantity']);

        /** @var $order Order */
        $order = Order::find($id);
        /** @var $history History */
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
            'price' => '$' . number_format($inventory_1->price_retail, 2),
            'order_link' => str_replace('{id}', $order->id, config('routes.front.bs_order_show_url')),
            'order_number' => $order->order_number,
        ]);

        $this->assertEquals($history->details['quantity'], [
            'old' => 20,
            'new' => 20 - $data['types_of_work'][0]['inventories'][0]['quantity'],
            'type' => 'updated',
        ]);
    }

    /** @test */
    public function success_create_with_attachment()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[Order::ATTACHMENT_FIELD_NAME] = [
            UploadedFile::fake()->image('img_1.png'),
            UploadedFile::fake()->image('img_2.png'),
        ];

        $id = $this->postJson(route('api.v1.orders.bs'), $data)
            ->assertJsonStructure([
                'data' => [
                    'attachments' => [
                        [
                            'id',
                            'name',
                            'file_name',
                            'mime_type',
                            'url',
                            'size',
                            'created_at',
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(2,'data.attachments')
            ->json('data.id')
        ;

        /** @var $model Order */
        $model = Order::find($id);
        $history = $model->histories[0];

        $this->assertEquals($history->msg, 'history.order.common.created');

        foreach ($model->getAttachments() as $media){
            $this->assertEquals($history->details["attachments.{$media->id}.name"], [
                'old' => null,
                'new' => $media->name,
                'type' => 'added',
            ]);
        }
    }

    /** @test */
    public function success_create_only_required_field()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $trailer = $this->trailerBuilder->create();
        $data['trailer_id'] = $trailer->id;

        unset(
            $data['truck_id'],
            $data['discount'],
            $data['tax_inventory'],
            $data['tax_labor'],
            $data['notes'],
            $data['need_to_update_prices'],
        );

        $data['types_of_work'][0]['save_to_the_list'] = true;

        $this->assertFalse(TypeOfWork::query()->where('name', $data['types_of_work'][0]['name'])->exists());

        $this->postJson(route('api.v1.orders.bs'), $data)
            ->assertJson([
                'data' => [
                    'vehicle' => [
                        'id' => $trailer->id,
                        'vehicle_form' => Trailer::MORPH_NAME
                    ],
                    'discount' => null,
                    'tax_labor' => null,
                    'tax_inventory' => null,
                    'notes' => null,
                ]
            ])
        ;

        /** @var $typeOfWork TypeOfWork */
        $typeOfWork = TypeOfWork::query()->where('name', $data['types_of_work'][0]['name'])->first();

        $this->assertEquals($typeOfWork->duration, $data['types_of_work'][0]['duration']);
        $this->assertEquals($typeOfWork->hourly_rate, $data['types_of_work'][0]['hourly_rate']);

        $this->assertCount(1, $typeOfWork->inventories);
        $this->assertEquals($typeOfWork->inventories[0]->inventory_id, $data['types_of_work'][0]['inventories'][0]['id']);
        $this->assertEquals($typeOfWork->inventories[0]->quantity, $data['types_of_work'][0]['inventories'][0]['quantity']);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['mechanic_id'] = null;

        $res = $this->postJson(route('api.v1.orders.bs'), $data, [
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

        $data = $this->data;

        $this->postJson(route('api.v1.orders.bs'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertFalse(Order::query()->where('notes', $data['notes'])->exists());
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.orders.bs'), $data)
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
    public function fail_create_not_mechanic()
    {
        $this->loginUserAsSuperAdmin();

        $user = $this->userBuilder->asAdmin()->create();

        $data = $this->data;
        $data['mechanic_id'] = $user->id;

        $res = $this->postJson(route('api.v1.orders.bs'), $data)
        ;

        self::assertValidationMsg($res, __('validation.custom.user.role.mechanic_not_found'), 'mechanic_id');
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
