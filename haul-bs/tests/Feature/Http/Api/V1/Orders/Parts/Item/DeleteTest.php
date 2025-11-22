<?php

namespace Feature\Http\Api\V1\Orders\Parts\Item;

use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected TransactionBuilder $transactionBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->transactionBuilder = resolve(TransactionBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();
        $this->itemBuilder->inventory($inventory)->order($model)->create();
        $this->itemBuilder->inventory($inventory)->order($model)->create();

        $this->assertCount(3, $model->items);

        $this->assertNull($model->total_amount);
        $this->assertNull($model->paid_amount);
        $this->assertNull($model->debt_amount);
        $this->assertFalse($model->source->isHaulkDepot());

        $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]))
            ->assertJsonCount(2, 'data');
        ;

        $model->refresh();

        $this->assertCount(2, $model->items);

        $this->assertNotNull($model->total_amount);
        $this->assertNotNull($model->paid_amount);
        $this->assertNotNull($model->debt_amount);

        Event::assertNotDispatched(RequestToEcom::class);
    }

    /** @test */
    public function success_delete_as_ecomm()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();
        $this->itemBuilder->inventory($inventory)->order($model)->create();
        $this->itemBuilder->inventory($inventory)->order($model)->create();

        $this->assertCount(3, $model->items);

        $this->assertNull($model->total_amount);
        $this->assertNull($model->paid_amount);
        $this->assertNull($model->debt_amount);
        $this->assertTrue($model->source->isHaulkDepot());

        $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]))
            ->assertJsonCount(2, 'data');
        ;

        $model->refresh();

        $this->assertCount(2, $model->items);

        $this->assertNotNull($model->total_amount);
        $this->assertNotNull($model->paid_amount);
        $this->assertNotNull($model->debt_amount);

        Event::assertDispatched(fn (RequestToEcom $event) =>
            $event->getModel()->id === $model->id
            && $event->getAction() == OrderPartsHistoryService::ACTION_UPDATE
        );
        Event::assertListening(
            RequestToEcom::class,
            RequestToEcomListener::class
        );
    }

    /** @test */
    public function success_delete_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();
        $itemId = $item->id;
        $this->itemBuilder->inventory($inventory)->order($model)->create();

        $this->assertEmpty($model->histories);
        $this->assertCount(2, $model->items);

        $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]))
        ;

        $model->refresh();

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.parts.delete_item');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            'inventory_name' => $inventory->name,
        ]);

        $this->assertEquals($history->details['items.'. $itemId . '.inventories.' . $inventory->id .'.name'], [
            'old' => $inventory->name,
            'new' => null,
            'type' => 'removed',
        ]);
        $this->assertEquals($history->details['items.'. $itemId . '.inventories.' . $inventory->id .'.quantity'], [
            'old' => 5,
            'new' => null,
            'type' => 'removed',
        ]);
    }

    /** @test */
    public function success_delete_not_history_if_draft_order()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $this->assertEmpty($model->histories);
        $this->assertCount(1, $model->items);

        $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]))
        ;

        $model->refresh();

        $this->assertEmpty($model->histories);
    }

    /** @test */
    public function success_delete_reduceReservedInOrder()
    {
        $user = $this->loginUserAsSuperAdmin();

        $wasQty = 10;
        $returnedQty = 5;

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity($wasQty)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty($returnedQty)
            ->order($model)
            ->create();
        $this->itemBuilder->inventory($inventory)->order($model)->create();

        $transaction = $this->transactionBuilder
            ->inventory($inventory)
            ->order($model)
            ->qty($returnedQty)
            ->is_reserve(true)
            ->create();
        $transactionId = $transaction->id;

        $this->assertEmpty($inventory->histories);

        $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]))
        ;

        $model->refresh();
        $inventory->refresh();

        $this->assertEquals($inventory->quantity, $wasQty + $returnedQty);
        $this->assertNull(Transaction::find($transactionId));

        $history_1 = $inventory->histories[0];

        $this->assertEquals($history_1->type, HistoryType::CHANGES);
        $this->assertEquals($history_1->user_id, $user->id);
        $this->assertEquals($history_1->msg, 'history.inventory.quantity_returned_for_deleted_order');
        $this->assertEquals($history_1->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory->stock_number,
            'inventory_name' => $inventory->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.parts_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEquals($history_1->details, [
            "quantity" => [
                'old' => $wasQty,
                'new' => $wasQty + $returnedQty,
                'type' => 'updated',
            ],
        ]);
    }

    /** @test */
    public function success_delete_reduceReservedInOrder_as_not_if_order_is_draft()
    {
        $user = $this->loginUserAsSuperAdmin();

        $wasQty = 10;
        $returnedQty = 5;

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity($wasQty)->create();
        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty($returnedQty)
            ->order($model)
            ->create();
        $this->itemBuilder->inventory($inventory)->order($model)->create();

        $transaction = $this->transactionBuilder
            ->inventory($inventory)
            ->order($model)
            ->qty($returnedQty)
            ->is_reserve(true)
            ->create();
        $transactionId = $transaction->id;

        $this->assertEmpty($inventory->histories);

        $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]))
        ;

        $model->refresh();
        $inventory->refresh();

        $this->assertEquals($inventory->quantity, $wasQty);
        $this->assertNotNull(Transaction::find($transactionId));
        $this->assertEmpty($inventory->histories);
    }

    /** @test */
    public function fail_delete_is_lats_item()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $this->assertCount(1, $model->items);

        $res = $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_delete_last_item"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_delete_order_is_paid()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $this->assertCount(1, $model->items);

        $res = $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_edit_paid"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_delete_order_is_returned()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(false)->status(OrderStatus::Returned())->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $this->assertCount(1, $model->items);

        $res = $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_edit"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id + 999,
            'itemId' => $item->id
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_item()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id +999
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found_item"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id,
            'itemId' => $item->id
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $item = $this->itemBuilder->inventory($inventory)
            ->qty(5)
            ->order($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete-item', [
            'id' => $model->id + 999,
            'itemId' => $item->id
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
