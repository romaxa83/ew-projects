<?php

namespace Feature\Http\Api\V1\Orders\BS\Action;

use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWorkInventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkInventoryBuilder;
use Tests\TestCase;

class RestoreTest extends TestCase
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
    public function success_restore()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        $this->postJson(route('api.v1.orders.bs.restore', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::In_process->value,
                ],
            ])
        ;

        $model->refresh();

        $this->assertNull($model->status_before_deleting);
        $this->assertNull($model->deleted_at);

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::ACTIVITY);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.bs.restored');

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
    public function success_restore_reserveForOrder()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity(20)->create();
        $inventory_2 = $this->inventoryBuilder->quantity(30)->create();

        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        $work_2 = $this->orderTypeOfWorkBuilder->order($model)->create();

        /** @var $work_inv_1 TypeOfWorkInventory */
        $work_inv_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->inventory($inventory_1)
            ->qty(5)
            ->create();
        $work_inv_2 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_2)
            ->inventory($inventory_2)
            ->qty(6)
            ->create();

        $this->postJson(route('api.v1.orders.bs.restore', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;

        $inventory_1->refresh();
        $inventory_2->refresh();

        $this->assertEquals($inventory_1->quantity, 20 - $work_inv_1->quantity);
        $history_1 = $inventory_1->histories[0];

        $transaction_1 = Transaction::query()
            ->where('order_id', $model->id)
            ->where('inventory_id', $inventory_1->id)
            ->first();

        $this->assertEquals($transaction_1->quantity, $work_inv_1->quantity);
        $this->asserttrue($transaction_1->operation_type->isSold());
        $this->asserttrue($transaction_1->is_reserve);

        $this->assertEquals($history_1->type, HistoryType::CHANGES);
        $this->assertEquals($history_1->user_id, $user->id);
        $this->assertEquals($history_1->msg, 'history.inventory.quantity_reserved_for_order');

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
        $this->assertEquals($history_1->details['quantity'], [
            "old" => 20,
            "new" => 20 - $work_inv_1->quantity,
            "type" => 'updated',
        ]);

        $this->assertEquals($inventory_2->quantity, 30 - $work_inv_2->quantity);
        $history_2 = $inventory_2->histories[0];

        $transaction_2 = Transaction::query()
            ->where('order_id', $model->id)
            ->where('inventory_id', $inventory_2->id)
            ->first();

        $this->assertEquals($transaction_2->quantity, $work_inv_2->quantity);
        $this->asserttrue($transaction_2->operation_type->isSold());
        $this->asserttrue($transaction_2->is_reserve);

        $this->assertEquals($history_2->type, HistoryType::CHANGES);
        $this->assertEquals($history_2->user_id, $user->id);
        $this->assertEquals($history_2->msg, 'history.inventory.quantity_reserved_for_order');

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
        $this->assertEquals($history_2->details['quantity'], [
            "old" => 30,
            "new" => 30 - $work_inv_2->quantity,
            "type" => 'updated',
        ]);
    }

    /** @test */
    public function fail_restore_enough_inventory()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Deleted->value)
            ->status_before_deleting(OrderStatus::In_process->value)
            ->deleted()
            ->create();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->quantity(2)->create();
        $inventory_2 = $this->inventoryBuilder->quantity(30)->create();

        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        $work_2 = $this->orderTypeOfWorkBuilder->order($model)->create();

        /** @var $work_inv_1 TypeOfWorkInventory */
        $work_inv_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->inventory($inventory_1)
            ->qty(5)
            ->create();
        $work_inv_2 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_2)
            ->inventory($inventory_2)
            ->qty(6)
            ->create();

        $res = $this->postJson(route('api.v1.orders.bs.restore', ['id' => $model->id]))
        ;

        self::assertValidationMsg($res, __('validation.custom.order.bs.not_enough_inventory_for_restore'), 'order');
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->postJson(route('api.v1.orders.bs.restore', ['id' => 9999999]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.bs.restore', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.bs.restore', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
