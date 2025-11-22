<?php

namespace Feature\Http\Api\V1\Orders\Parts\Catalog;

use App\Enums\Orders\Parts\OrderPaymentStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentStatusesTest extends TestCase
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

        $this->getJson(route('api.v1.orders.parts.catalog.payment-statuses'))
            ->assertJson([
                'data' => [
                    ['key' => OrderPaymentStatus::Paid->value, 'title' => OrderPaymentStatus::Paid->label()],
                    ['key' => OrderPaymentStatus::Not_paid->value, 'title' => OrderPaymentStatus::Not_paid->label()],
                    ['key' => OrderPaymentStatus::Refunded->value, 'title' => OrderPaymentStatus::Refunded->label()],
                ],
            ])
            ->assertJsonCount(count(OrderPaymentStatus::cases()), 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.parts.catalog.payment-methods'));

        self::assertUnauthenticatedMessage($res);
    }
}
