<?php

namespace Tests\Feature\Http\Api\V1\Orders\BS\Crud;

use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\BS\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkInventoryBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected TransactionBuilder $transactionBuilder;
    protected OrderTypeOfWorkBuilder $orderTypeOfWorkBuilder;
    protected OrderTypeOfWorkInventoryBuilder $orderTypeOfWorkInventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->transactionBuilder = resolve(TransactionBuilder::class);
        $this->orderTypeOfWorkBuilder = resolve(OrderTypeOfWorkBuilder::class);
        $this->orderTypeOfWorkInventoryBuilder = resolve(OrderTypeOfWorkInventoryBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $id = $model->id;
        $oldStatus = $model->status;

        $this->deleteJson(route('api.v1.orders.bs.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $model->refresh();

        $this->assertEquals($model->status_before_deleting, $oldStatus);
        $this->assertTrue($model->status->isDeleted());
        $this->assertNotNull($model->deleted_at);

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.common.deleted');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);
        $this->assertEmpty($history->details);
    }

    /** @test */
    public function success_delete_reduceReservedInOrder()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity(20)->create();
        $inventory_2 = $this->inventoryBuilder->quantity(20)->create();

        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();

        $work_inv_1 = $this->orderTypeOfWorkInventoryBuilder
            ->inventory($inventory_1)
            ->type_of_work($work_1)
            ->qty(10)
            ->create();
        $work_inv_2 = $this->orderTypeOfWorkInventoryBuilder
            ->inventory($inventory_2)
            ->type_of_work($work_1)
            ->qty(11)
            ->create();

        $transaction_1 = $this->transactionBuilder
            ->inventory($inventory_1)
            ->order($model)
            ->qty(10)
            ->is_reserve(true)
            ->create();
        $transactionId_1 = $transaction_1->id;
        $transaction_2 = $this->transactionBuilder
            ->inventory($inventory_2)
            ->order($model)
            ->qty(11)
            ->is_reserve(true)
            ->create();
        $transactionId_2 = $transaction_2->id;

        $this->deleteJson(route('api.v1.orders.bs.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $model->refresh();

        $inventory_1->refresh();
        $inventory_2->refresh();

        $this->assertEquals($inventory_1->quantity, 20 + 10);
        $this->assertEquals($inventory_2->quantity, 20 + 11);

        $this->assertNull(Transaction::find($transactionId_1));
        $this->assertNull(Transaction::find($transactionId_2));

        $history_1 = $inventory_1->histories[0];

        $this->assertEquals($history_1->type, HistoryType::CHANGES);
        $this->assertEquals($history_1->user_id, $user->id);
        $this->assertEquals($history_1->msg, 'history.inventory.quantity_returned_for_deleted_order');
        $this->assertEquals($history_1->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_1->stock_number,
            'inventory_name' => $inventory_1->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($work_inv_1->price, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.bs_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEquals($history_1->details, [
            "quantity" => [
                'old' => 20,
                'new' => 20 + 10,
                'type' => 'updated',
            ],
        ]);

        $history_2 = $inventory_2->histories[0];

        $this->assertEquals($history_2->type, HistoryType::CHANGES);
        $this->assertEquals($history_2->user_id, $user->id);
        $this->assertEquals($history_2->msg, 'history.inventory.quantity_returned_for_deleted_order');
        $this->assertEquals($history_2->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_2->stock_number,
            'inventory_name' => $inventory_2->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($work_inv_2->price, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.bs_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEquals($history_2->details, [
            "quantity" => [
                'old' => 20,
                'new' => 20 + 11,
                'type' => 'updated',
            ],
        ]);
    }

    /** @test */
    public function fail_status_finished()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::Finished->value)->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete', ['id' => $model->id]));

        self::assertErrorMsg($res, __("exceptions.orders.bs.finished_order_cant_be_deleted"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
