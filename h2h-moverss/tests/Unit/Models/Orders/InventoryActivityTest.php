<?php

namespace Tests\Unit\Models\Orders;

use App\Enums\ActionEnum;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\InventoryActivityBuilder;
use Tests\Builders\Orders\InventoryBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class InventoryActivityTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected InventoryActivityBuilder $inventoryActivityBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->inventoryActivityBuilder = resolve(InventoryActivityBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function save_one_as_create_and_as_user()
    {
        $user = $this->loginUser();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = [
            [
                "id" => null,
                "is_section" => 0,
                "price" => null,
                "qty" => 5,
                "weight" => 210.0,
                "volume" => 30.0,
                "title" => "Pilates Machine",
                "sort" => 1,
                "item_id" => null,
            ]
        ];

        $this->assertEmpty(Order\InventoryActivity::get());

        (new Order\InventoryActivity())->createRecords($order, $data);

        $model = Order\InventoryActivity::first();

        $this->assertEquals($model->order_id, $order->id);
        $this->assertEquals($model->client_id, $order->client_id);
        $this->assertEquals($model->user_id, $user->id);
        $this->assertFalse($model->is_client_action);
        $this->assertEquals($model->action, ActionEnum::Create);
        $this->assertEquals($model->miscs, [
            'inventory_id' => null,
            'title' => $data[0]['title'],
            'qty' => $data[0]['qty'],
        ]);
    }

    /** @test */
    public function save_one_as_create_and_as_client()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = [
            [
                "id" => null,
                "is_section" => 0,
                "price" => null,
                "qty" => 5,
                "weight" => 210.0,
                "volume" => 30.0,
                "title" => "Pilates Machine",
                "sort" => 1,
                "item_id" => null,
            ]
        ];

        $this->assertEmpty(Order\InventoryActivity::get());

        (new Order\InventoryActivity())->createRecords($order, $data);

        $model = Order\InventoryActivity::first();

        $this->assertEquals($model->order_id, $order->id);
        $this->assertEquals($model->client_id, $order->client_id);
        $this->assertNull($model->user_id);
        $this->assertTrue($model->is_client_action);
        $this->assertEquals($model->action, ActionEnum::Create);

    }

    /** @test */
    public function save_one_as_update_and_as_user()
    {
        $user = $this->loginUser();

        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = [
            [
                "id" => 11,
                "is_section" => 0,
                "price" => null,
                "qty" => 5,
                "weight" => 210.0,
                "volume" => 30.0,
                "title" => "Pilates Machine",
                "sort" => 1,
                "item_id" => null,
            ]
        ];

        $this->assertEmpty(Order\InventoryActivity::get());

        (new Order\InventoryActivity())->createRecords($order, $data);

        $model = Order\InventoryActivity::first();

        $this->assertEquals($model->order_id, $order->id);
        $this->assertEquals($model->client_id, $order->client_id);
        $this->assertEquals($model->user_id, $user->id);
        $this->assertFalse($model->is_client_action);
        $this->assertEquals($model->action, ActionEnum::Update);
        $this->assertEquals($model->miscs, [
            'inventory_id' => $data[0]['id'],
            'title' => $data[0]['title'],
            'qty' => $data[0]['qty'],
        ]);
    }

    /** @test */
    public function save_one_as_update_and_as_client()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $data = [
            [
                "id" => 11,
                "is_section" => 0,
                "price" => null,
                "qty" => 5,
                "weight" => 210.0,
                "volume" => 30.0,
                "title" => "Pilates Machine",
                "sort" => 1,
                "item_id" => null,
            ]
        ];

        $this->assertEmpty(Order\InventoryActivity::get());

        (new Order\InventoryActivity())->createRecords($order, $data);

        $model = Order\InventoryActivity::first();

        $this->assertEquals($model->order_id, $order->id);
        $this->assertEquals($model->client_id, $order->client_id);
        $this->assertNull($model->user_id);
        $this->assertTrue($model->is_client_action);
        $this->assertEquals($model->action, ActionEnum::Update);
        $this->assertEquals($model->miscs, [
            'inventory_id' => $data[0]['id'],
            'title' => $data[0]['title'],
            'qty' => $data[0]['qty'],
        ]);
    }

    /** @test */
    public function save_one_as_delete()
    {
        $user = $this->loginUser();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $i_1 = $this->inventoryBuilder->order($order)->create();
        $i_2 = $this->inventoryBuilder->order($order)->create();

        $now = CarbonImmutable::now();

        $i_a_1 = $this->inventoryActivityBuilder
            ->order($order)
            ->miscs([
                'inventory_id' => $i_1->id,
                'title' => $i_1->title,
                'qty' => 4,
            ])
            ->created($now->subMinutes(20))
            ->create();
        $i_a_2 = $this->inventoryActivityBuilder
            ->order($order)
            ->miscs([
                'inventory_id' => $i_1->id,
                'title' => $i_1->title,
                'qty' => 6,
            ])
            ->created($now->subMinutes(2))
            ->create();

        $data = [
            [
                "id" => $i_2->id,
                "is_section" => 0,
                "price" => null,
                "qty" => 5,
                "weight" => 210.0,
                "volume" => 30.0,
                "title" => "Pilates Machine",
                "sort" => 1,
                "item_id" => null,
            ]
        ];

        (new Order\InventoryActivity())->createRecords($order, $data);

        $model = Order\InventoryActivity::query()
            ->where('action', ActionEnum::Delete())
            ->first();

        $this->assertEquals($model->order_id, $order->id);
        $this->assertEquals($model->client_id, $order->client_id);
        $this->assertEquals($model->action, ActionEnum::Delete);
        $this->assertEquals($model->miscs, [
            'inventory_id' => $i_1->id,
            'title' => $i_1->title,
            'qty' => 6,
        ]);
    }

}
