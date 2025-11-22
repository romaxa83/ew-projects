<?php

namespace Feature\Http\Api\V1\Orders\BS\Action;

use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\TypeOfWorkInventory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\TransactionBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkInventoryBuilder;
use Tests\TestCase;

class ChangeStatusTest extends TestCase
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
    public function success_update()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data['status'] = OrderStatus::In_process->value;

        $this->assertTrue($model->status->isNew());
        $this->assertNull($model->status_changed_at);

        $this->postJson(route('api.v1.orders.bs.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::In_process->value,
                ],
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->status_changed_at);

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.common.status_changed');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            'status' => OrderStatus::In_process->label(),
        ]);

        $this->assertEquals($history->details['status'], [
            'old' => OrderStatus::New->value,
            'new' => OrderStatus::In_process->value,
            'type' => 'updated',
        ]);
    }

    /** @test */
    public function success_update_reserveOnMovingOrderFromFinished()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::Finished->value, CarbonImmutable::now())->create();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        /** @var $transaction_1 Transaction */
        $transaction_1 = $this->transactionBuilder->inventory($inventory_1)->order($model)->create();
        $transaction_2 = $this->transactionBuilder->inventory($inventory_2)->order($model)->create();

        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();
        $work_2 = $this->orderTypeOfWorkBuilder->order($model)->create();

        $work_inv_1 = $this->orderTypeOfWorkInventoryBuilder->type_of_work($work_1)->inventory($inventory_1)->create();
        $work_inv_2 = $this->orderTypeOfWorkInventoryBuilder->type_of_work($work_2)->inventory($inventory_2)->create();

        $data['status'] = OrderStatus::In_process->value;

        $this->assertFalse($transaction_1->is_reserve);
        $this->assertFalse($transaction_2->is_reserve);

        $this->postJson(route('api.v1.orders.bs.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::In_process->value,
                ],
            ])
        ;

        $transaction_1->refresh();
        $transaction_2->refresh();

        $this->assertTrue($transaction_1->is_reserve);
        $this->assertTrue($transaction_2->is_reserve);

        $inventory_1->refresh();
        $inventory_2->refresh();

        $history_1 = $inventory_1->histories[0];

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
            'price' => '$' . number_format($inventory_1->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.bs_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_1->details);

        $history_2 = $inventory_2->histories[0];

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
            'price' => '$' . number_format($inventory_2->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.bs_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_2->details);
    }

    /** @test */
    public function success_update_finishedOrderWithInventory()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $inventory_1 Inventory */
        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();

        /** @var $work_inv_1 TypeOfWorkInventory */
        $work_inv_1 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->price(10)
            ->qty(11)
            ->inventory($inventory_1)
            ->create();
        $work_inv_2 = $this->orderTypeOfWorkInventoryBuilder
            ->type_of_work($work_1)
            ->inventory($inventory_2)
            ->price(2)
            ->qty(13)
            ->create();

        /** @var $transaction_1 Transaction */
        $transaction_1 = $this->transactionBuilder
            ->inventory($inventory_1)
            ->order($model)
            ->price($work_inv_1->price)
            ->qty($work_inv_1->quantity)
            ->is_reserve(true)
            ->create();
        $transaction_id_1 = $transaction_1->id;

        $transaction_2 = $this->transactionBuilder
            ->inventory($inventory_2)
            ->order($model)
            ->price($work_inv_2->price)
            ->qty($work_inv_2->quantity)
            ->is_reserve(true)
            ->create();
        $transaction_id_2 = $transaction_2->id;

        $data['status'] = OrderStatus::Finished->value;

        $this->postJson(route('api.v1.orders.bs.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Finished->value,
                ],
            ])
        ;

        $this->assertNull(Transaction::find($transaction_id_1));
        $this->assertNull(Transaction::find($transaction_id_2));

        /** @var $new_transaction_1 Transaction */
        $new_transaction_1 = Transaction::query()->where('order_id', $model->id)->where('inventory_id', $inventory_1->id)->first();

        $this->assertTrue($new_transaction_1->operation_type->isSold());
        $this->assertFalse($new_transaction_1->is_reserve);
        $this->assertEquals($new_transaction_1->price, $work_inv_1->price);
        $this->assertEquals($new_transaction_1->quantity, $work_inv_1->quantity);

        /** @var $new_transaction_2 Transaction */
        $new_transaction_2 = Transaction::query()->where('order_id', $model->id)->where('inventory_id', $inventory_2->id)->first();
        $this->assertTrue($new_transaction_2->operation_type->isSold());
        $this->assertFalse($new_transaction_2->is_reserve);
        $this->assertEquals($new_transaction_2->price, $work_inv_2->price);
        $this->assertEquals($new_transaction_2->quantity, $work_inv_2->quantity);


        $inventory_1->refresh();
        $inventory_2->refresh();

        $history_1 = $inventory_1->histories[0];

        $this->assertEquals($history_1->type, HistoryType::ACTIVITY);
        $this->assertEquals($history_1->user_id, $user->id);
        $this->assertEquals($history_1->msg, 'history.inventory.finished_order');

        $this->assertEquals($history_1->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_1->stock_number,
            'inventory_name' => $inventory_1->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory_1->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.bs_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_1->details);

        $history_2 = $inventory_2->histories[0];

        $this->assertEquals($history_2->type, HistoryType::ACTIVITY);
        $this->assertEquals($history_2->user_id, $user->id);
        $this->assertEquals($history_2->msg, 'history.inventory.finished_order');

        $this->assertEquals($history_2->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory_2->stock_number,
            'inventory_name' => $inventory_2->name,
            'user_id' => $user->id,
            'price' => '$' . number_format($inventory_2->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.bs_order_show_url')),
            'order_number' => $model->order_number,
        ]);
        $this->assertEmpty($history_2->details);

    }

    /** @test */
    public function success_toggle_to_finish()
    {
        $this->loginUserAsSuperAdmin();

        $inventory_1 = $this->inventoryBuilder->quantity(10)->create();

        /** @var $model Order */
        $model = $this->orderBuilder->total_amount(1000)->create();

        $transaction = $this->transactionBuilder->inventory($inventory_1)
            ->operation_type(OperationType::PURCHASE)->qty(10)->create();

        $work_1 = $this->orderTypeOfWorkBuilder->order($model)->create();

        $this->orderTypeOfWorkInventoryBuilder->inventory($inventory_1)
            ->type_of_work($work_1)->qty(2)->create();

        $data['status'] = OrderStatus::Finished();

        $this->assertTrue($model->status->isNew());
        $this->assertNull($model->status_changed_at);
        $this->assertNull($model->profit);
        $this->assertNull($model->parts_cost);

        $this->postJson(route('api.v1.orders.bs.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Finished(),
                ],
            ])
        ;

        $model->refresh();

        $partCost = round($model->getPartsCost(), 2);
        $this->assertEquals($model->parts_cost, $partCost);
        $this->assertEquals($model->profit, round($model->total_amount - $partCost, 2));
    }

    /** @test */
    public function success_toggle_from_finish()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->total_amount(1000)
            ->status(OrderStatus::Finished(), CarbonImmutable::now())
            ->parts_cost(100)
            ->profit(10)
            ->create();

        $data['status'] = OrderStatus::In_process();

        $this->assertNotNull($model->profit);
        $this->assertNotNull($model->parts_cost);

        $this->postJson(route('api.v1.orders.bs.change-status', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::In_process(),
                ],
            ])
        ;

        $model->refresh();

        $this->assertNull($model->profit);
        $this->assertNull($model->parts_cost);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.orders.bs.change-status', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['status', null, 'validation.required', ['attribute' => 'validation.attributes.status']],
            ['status', 'wrong', 'validation.in', ['attribute' => 'validation.attributes.status']],
            ['status', OrderStatus::New->value, 'validation.in', ['attribute' => 'validation.attributes.status']],
        ];
    }

    /** @test */
    public function fail_cant_be_changed()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->status(OrderStatus::Finished->value, CarbonImmutable::now()->subDays(2))
            ->create();

        $data['status'] = OrderStatus::In_process->value;

        $res = $this->postJson(route('api.v1.orders.bs.change-status', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res, __(__('exceptions.orders.status_cant_be_change')), 'status');
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data['status'] = OrderStatus::In_process->value;

        $res = $this->postJson(route('api.v1.orders.bs.change-status', ['id' => 9999999]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $data['status'] = OrderStatus::In_process->value;

        $res = $this->postJson(route('api.v1.orders.bs.change-status', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $data['status'] = OrderStatus::In_process->value;

        $res = $this->postJson(route('api.v1.orders.bs.change-status', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
