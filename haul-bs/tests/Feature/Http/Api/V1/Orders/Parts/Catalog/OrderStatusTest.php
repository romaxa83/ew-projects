<?php

namespace Feature\Http\Api\V1\Orders\Parts\Catalog;

use App\Enums\Orders\Parts\OrderStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.orders.parts.catalog.order-statuses'))
            ->assertJson([
                'data' => [
                    ['key' => OrderStatus::New->value, 'title' => OrderStatus::New->label()],
                    ['key' => OrderStatus::In_process->value, 'title' => OrderStatus::In_process->label()],
                    ['key' => OrderStatus::Sent->value, 'title' => OrderStatus::Sent->label()],
                    ['key' => OrderStatus::Pending_pickup->value, 'title' => OrderStatus::Pending_pickup->label()],
                    ['key' => OrderStatus::Delivered->value, 'title' => OrderStatus::Delivered->label()],
                    ['key' => OrderStatus::Canceled->value, 'title' => OrderStatus::Canceled->label()],
                    ['key' => OrderStatus::Returned->value, 'title' => OrderStatus::Returned->label()],
                    ['key' => OrderStatus::Lost->value, 'title' => OrderStatus::Lost->label()],
                    ['key' => OrderStatus::Damaged->value, 'title' => OrderStatus::Damaged->label()],
                ],
            ])
            ->assertJsonCount(count(OrderStatus::cases()), 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.parts.catalog.order-statuses'));

        self::assertUnauthenticatedMessage($res);
    }
}
