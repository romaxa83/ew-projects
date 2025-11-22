<?php

namespace Feature\Http\Api\V1\Orders\Parts\Action;

use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Users\UserStatus;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Orders\Parts\Order;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class AssignSalesManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_assign_sales_manager()
    {
        Event::fake([RequestToEcom::class]);

        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $sales User */
        $sales = $this->userBuilder->asSalesManager()->create();

        $data['sales_manager_id'] = $sales->id;

        $this->assertNull($model->sales_manager_id);
        $this->assertTrue($model->status->isNew());
        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::In_process(),
                    'sales_manager' => [
                        'id' => $sales->id
                    ],
                ],
            ])
        ;

        /** @var $model Order */
        $model->refresh();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.parts.assign_sales_manager');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            'sales_manager_name' => $sales->full_name,
        ]);

        $this->assertEquals($history->details['sales_manager'], [
            'old' => null,
            'new' => $sales->full_name,
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['status'], [
            'old' => OrderStatus::New(),
            'new' => OrderStatus::In_process(),
            'type' => 'updated',
        ]);

        Event::assertNotDispatched(RequestToEcom::class);
    }

    /** @test */
    public function success_assign_sales_manager_senn_to_ecom()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->source(OrderSource::Haulk_Depot)->create();

        /** @var $sales User */
        $sales = $this->userBuilder->asSalesManager()->create();

        $data['sales_manager_id'] = $sales->id;

        $this->assertNull($model->sales_manager_id);
        $this->assertTrue($model->status->isNew());

        $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::In_process(),
                    'sales_manager' => [
                        'id' => $sales->id
                    ],
                ],
            ])
        ;

        Event::assertDispatched(fn (RequestToEcom $event) =>
            $event->getModel()->id === $model->id
            && $event->getAction() == OrderPartsHistoryService::ACTION_STATUS_CHANGED
        );
        Event::assertListening(
            RequestToEcom::class,
            RequestToEcomListener::class
        );
    }

    /** @test */
    public function success_assign_but_not_change_status()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::Delivered())->create();

        /** @var $sales User */
        $sales = $this->userBuilder->asSalesManager()->create();

        $data['sales_manager_id'] = $sales->id;

        $this->assertTrue($model->status->isDelivered());

        $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::Delivered(),
                ],
            ])
        ;

        /** @var $model Order */
        $model->refresh();
        $history = $model->histories[0];

        $this->assertCount(1, $history->details);
        $this->assertEquals($history->details['sales_manager'], [
            'old' => null,
            'new' => $sales->full_name,
            'type' => 'updated',
        ]);
    }

    /** @test */
    public function success_reassign_sales_manager()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $sales User */
        $sales = $this->userBuilder->asSalesManager()->create();
        $salesAnother = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->sales_manager($salesAnother)->create();

        /** @var $sales User */
        $sales = $this->userBuilder->asSalesManager()->create();

        $data['sales_manager_id'] = $sales->id;

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'sales_manager' => [
                        'id' => $sales->id
                    ],
                ],
            ])
        ;

        /** @var $model Order */
        $model->refresh();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.parts.reassign_sales_manager');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            'sales_manager_name' => $sales->full_name,
        ]);

        $this->assertEquals($history->details['sales_manager'], [
            'old' => $salesAnother->full_name,
            'new' => $sales->full_name,
            'type' => 'updated',
        ]);
    }

    /** @test */
    public function success_assign_to_self()
    {
        /** @var $user User */
        $user = $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data['sales_manager_id'] = $user->id;

        $this->assertNull($model->sales_manager_id);
        $this->assertTrue($model->status->isNew());

        $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::In_process(),
                    'sales_manager' => [
                        'id' => $user->id
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function fail_user_not_sales_manager()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $mechanic User */
        $mechanic = $this->userBuilder->asMechanic()->create();
        $data['sales_manager_id'] = $mechanic->id;

        $res = $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res, __('validation.custom.user.role.sales_manager_not_found'), 'sales_manager_id');
    }

    /** @test */
    public function fail_sales_manager_is_not_active()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $sales User */
        $sales = $this->userBuilder->asSalesManager()->status(UserStatus::PENDING())->create();
        $data['sales_manager_id'] = $sales->id;

        $res = $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res, __('validation.custom.user.is_not_active'), 'sales_manager_id');
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

        $res = $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['sales_manager_id', null, 'validation.required', ['attribute' => 'validation.attributes.sales_manager_id']],
            ['sales_manager_id', 99999, 'validation.exists', ['attribute' => 'validation.attributes.sales_manager_id']],
        ];
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $user User */
        $user = $this->userBuilder->asSalesManager()->create();
        $data['sales_manager_id'] = $user->id;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id + 1]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $user User */
        $user = $this->userBuilder->asSalesManager()->create();
        $data['sales_manager_id'] = $user->id;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $user User */
        $user = $this->userBuilder->asSalesManager()->create();
        $data['sales_manager_id'] = $user->id;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.assign-sales-manager', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
