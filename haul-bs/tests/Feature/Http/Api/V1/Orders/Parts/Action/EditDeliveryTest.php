<?php

namespace Feature\Http\Api\V1\Orders\Parts\Action;

use App\Enums\Orders\Parts\DeliveryMethod;
use App\Enums\Orders\Parts\OrderStatus;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\Parts\Delivery;
use App\Models\Orders\Parts\Order;
use Tests\Builders\Orders\Parts\DeliveryBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class EditDeliveryTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected DeliveryBuilder $deliveryBuilder;

    protected array $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->deliveryBuilder = resolve(DeliveryBuilder::class);

        $this->data = [
            'delivery_method' => DeliveryMethod::Our_delivery(),
            'delivery_cost' => 2.9,
            'date_sent' =>CarbonImmutable::now()->format('Y-m-d'),
            'tracking_number' => '67686',
        ];
    }

    /** @test */
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $delivery Delivery */
        $delivery = $this->deliveryBuilder->order($model)->create();

        $data = $this->data;

        $this->assertNotEquals($delivery->method->value, $data['delivery_method']);
        $this->assertNotEquals($delivery->cost, $data['delivery_cost']);
        $this->assertNotEquals($delivery->tracking_number, $data['tracking_number']);

        $this->postJson(route('api.v1.orders.parts.delivery-update', [
            'id' => $model->id,
            'deliveryId' => $delivery->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'deliveries' => [
                        [
                            'id' => $delivery->id,
                            'delivery_method' => $data['delivery_method'],
                            'delivery_cost' => $data['delivery_cost'],
                            'tracking_number' => $data['tracking_number'],
                        ]
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $delivery Delivery */
        $delivery = $this->deliveryBuilder->order($model)->create();
        $old = clone $delivery;

        $data = $this->data;

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.delivery-update', [
            'id' => $model->id,
            'deliveryId' => $delivery->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;

        $model->refresh();
        $delivery->refresh();

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.parts.update_delivery');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

        $this->assertEquals($history->details['delivery.'.$delivery->id.'.delivery_method'], [
            'old' => $old->method->value,
            'new' => $data['delivery_method'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery.'.$delivery->id.'.delivery_cost'], [
            'old' => $old->cost,
            'new' => $data['delivery_cost'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery.'.$delivery->id.'.tracking_number'], [
            'old' => $old->tracking_number,
            'new' => $data['tracking_number'],
            'type' => 'updated',
        ]);

        $this->assertCount(4, $history->details);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $delivery Delivery */
        $delivery = $this->deliveryBuilder->order($model)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.parts.delivery-update', [
            'id' => $model->id + 1,
            'deliveryId' => $delivery->id
        ]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();
        /** @var $delivery Delivery */
        $delivery = $this->deliveryBuilder->order($model)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.parts.delivery-update', [
            'id' => $model->id,
            'deliveryId' => $delivery->id
        ]), $data)
        ;

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();
        /** @var $delivery Delivery */
        $delivery = $this->deliveryBuilder->order($model)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.parts.delivery-update', [
            'id' => $model->id,
            'deliveryId' => $delivery->id
        ]), $data)
        ;

        self::assertUnauthenticatedMessage($res);
    }
}
