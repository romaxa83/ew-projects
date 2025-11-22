<?php

namespace Feature\Http\Api\V1\Orders\Parts\Crud;

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
    protected InventoryBuilder $inventoryBuilder;
    protected ItemBuilder $itemBuilder;
    protected TransactionBuilder $transactionBuilder;
    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->transactionBuilder = resolve(TransactionBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Event::fake([RequestToEcom::class]);

        $user = $this->loginUserAsSuperAdmin();

        $was = 10;
        $returned = 2;

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity($was)->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->itemBuilder->qty($returned)->order($model)
            ->inventory($inventory)->create();
        $transaction = $this->transactionBuilder
            ->inventory($inventory)
            ->order($model)
            ->qty($returned)
            ->is_reserve(true)
            ->create();
        $transactionId = $transaction->id;

        $oldStatus = $model->status;

        $this->assertEmpty($inventory->histories);
        $this->assertFalse($model->source->isHaulkDepot());

        $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $model->refresh();
        $inventory->refresh();

        $this->assertEquals($model->status_before_deleting, $oldStatus);
        $this->assertNotNull($model->deleted_at);

        $this->assertEquals($inventory->quantity, $was + $returned);
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
                'old' => $was,
                'new' => $was + $returned,
                'type' => 'updated',
            ],
        ]);

        Event::assertNotDispatched(RequestToEcom::class);
    }

    /** @test */
    public function success_delete_as_draft()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();
        $modelId = $model->id;

        $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertNull(Order::find($modelId));

        Event::assertNotDispatched(RequestToEcom::class);
    }

    /** @test */
    public function success_delete_event_send_ecomm()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        $this->assertTrue($model->source->isHaulkDepot());

        $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $model->refresh();
        $this->assertNotNull($model->deleted_at);

        Event::assertDispatched(fn (RequestToEcom $event) =>
            $event->getModel()->id === $model->id
            && $event->getAction() == OrderPartsHistoryService::ACTION_DELETE
        );
        Event::assertListening(
            RequestToEcom::class,
            RequestToEcomListener::class
        );
    }

    /** @test */
    public function fail_order_is_paid()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(true)->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]));

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_delete_paid"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_order_status_is_sent()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::Sent())->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]));

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_delete"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_owner_sales()
    {
        $user = $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->sales_manager($user)->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function success_delete_as_draft_by_admin()
    {
        $this->loginUserAsAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();
        $modelId = $model->id;

        $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertNull(Order::find($modelId));
    }

    /** @test */
    public function fail_delete_not_draft_by_admin()
    {
        $this->loginUserAsAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(false)->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]))
        ;

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function success_delete_as_draft_by_sales_manager()
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();
        $modelId = $model->id;

        $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertNull(Order::find($modelId));
    }

    /** @test */
    public function fail_delete_not_draft_by_sales_manager()
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(false)->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]))
        ;

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->deleteJson(route('api.v1.orders.parts.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
