<?php

namespace Feature\Http\Api\V1\Orders\Parts\Catalog;

use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentTerms;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class StatusesToSwitchTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function statuses_for_new()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder->status(OrderStatus::New())->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJson([
                'data' => [
                    ['key' => OrderStatus::In_process->value, 'title' =>OrderStatus::In_process->label()],
                    ['key' => OrderStatus::Canceled->value, 'title' =>OrderStatus::Canceled->label()],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function statuses_for_in_process()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Delivery)
            ->status(OrderStatus::In_process())
            ->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJson([
                'data' => [
                    ['key' => OrderStatus::Pending_pickup->value, 'title' =>OrderStatus::Pending_pickup->label()],
                    ['key' => OrderStatus::Canceled->value, 'title' =>OrderStatus::Canceled->label()],
                    ['key' => OrderStatus::Sent->value, 'title' =>OrderStatus::Sent->label()],
                ],
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function statuses_for_in_process_if_delivery_type_as_pickup()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Pickup)
            ->status(OrderStatus::In_process())
            ->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJson([
                'data' => [
                    ['key' => OrderStatus::Pending_pickup->value, 'title' =>OrderStatus::Pending_pickup->label()],
                    ['key' => OrderStatus::Canceled->value, 'title' =>OrderStatus::Canceled->label()],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function statuses_for_in_process_not_paid_and_immediately()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder
            ->status(OrderStatus::In_process())
            ->is_paid(false)
            ->payment_terms(PaymentTerms::Immediately())
            ->delivery_type(DeliveryType::Delivery)
            ->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJson([
                'data' => [
                    ['key' => OrderStatus::Pending_pickup->value, 'title' =>OrderStatus::Pending_pickup->label()],
                    ['key' => OrderStatus::Canceled->value, 'title' =>OrderStatus::Canceled->label()],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function statuses_for_in_process_not_paid_and_from_ecomm()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder
            ->status(OrderStatus::In_process())
            ->source(OrderSource::Haulk_Depot)
            ->is_paid(false)
            ->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJson([
                'data' => [
                    ['key' => OrderStatus::Pending_pickup->value, 'title' =>OrderStatus::Pending_pickup->label()],
                    ['key' => OrderStatus::Canceled->value, 'title' =>OrderStatus::Canceled->label()],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function statuses_for_pending_pickup()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder->status(OrderStatus::Pending_pickup())->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJson([
                'data' => [
                    ['key' => OrderStatus::Delivered->value, 'title' =>OrderStatus::Delivered->label()],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function statuses_for_sent()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder->status(OrderStatus::Sent())->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJson([
                'data' => [
                    ['key' => OrderStatus::Delivered->value, 'title' =>OrderStatus::Delivered->label()],
                    ['key' => OrderStatus::Lost->value, 'title' =>OrderStatus::Lost->label()],
                    ['key' => OrderStatus::Damaged->value, 'title' =>OrderStatus::Damaged->label()],
                ],
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function statuses_for_delivered()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder
            ->status(OrderStatus::Delivered(), CarbonImmutable::now())
            ->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJson([
                'data' => [
                    ['key' => OrderStatus::Returned->value, 'title' =>OrderStatus::Returned->label()],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function statuses_for_delivered_empty()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder
            ->status(
                OrderStatus::Delivered(),
                CarbonImmutable::now()->subDays(config('orders.parts.change_status_delivered_to_returned') + 1)
            )
            ->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function statuses_for_canceled()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder->status(OrderStatus::Canceled())->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function statuses_for_returned()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder->status(OrderStatus::Returned())->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function statuses_for_lost()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder->status(OrderStatus::Lost())->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function statuses_for_damaged()
    {
        $this->loginUserAsSuperAdmin();

        $model = $this->orderBuilder->status(OrderStatus::Damaged())->create();

        $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', [
            'id' => $model->id,
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $model = $this->orderBuilder->status(OrderStatus::New())->create();

        $res = $this->getJson(route('api.v1.orders.parts.catalog.status-to-switch', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
