<?php

namespace Tests\Feature\Queries\Order;

use App\Types\Order\PaymentStatus;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class OrderPaymentStatusTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_LIST])
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr())->assertOk();

        $responseData = $response->json('data.orderPaymentStatuses');

        $this->assertCount(4, $responseData);

        $this->assertEquals(PaymentStatus::NONE, $responseData[0]['key']);
        $this->assertEquals(__('translation.order.payment.none'), $responseData[0]['name']);

        $this->assertEquals(PaymentStatus::NOT, $responseData[1]['key']);
        $this->assertEquals(__('translation.order.payment.not'), $responseData[1]['name']);

        $this->assertEquals(PaymentStatus::PART, $responseData[2]['key']);
        $this->assertEquals(__('translation.order.payment.part'), $responseData[2]['name']);

        $this->assertEquals(PaymentStatus::FULL, $responseData[3]['key']);
        $this->assertEquals(__('translation.order.payment.full'), $responseData[3]['name']);
    }

    public static function getQueryStr(): string
    {
        return "{orderPaymentStatuses {key, name}}";
    }
}
