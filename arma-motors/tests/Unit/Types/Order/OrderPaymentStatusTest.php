<?php

namespace Tests\Unit\Types\Order;

use App\Traits\AssetData;
use App\Types\Order\PaymentStatus;
use Tests\TestCase;

class OrderPaymentStatusTest extends TestCase
{
    use AssetData;

    /** @test */
    public function get_list()
    {
        $this->assertNotEmpty(PaymentStatus::list());
        $this->assertIsArray(PaymentStatus::list());
        $this->assertCount(4, PaymentStatus::list());

        $this->assertEquals(PaymentStatus::list()[PaymentStatus::NONE], __('translation.order.payment.none'));
        $this->assertEquals(PaymentStatus::list()[PaymentStatus::NOT], __('translation.order.payment.not'));
        $this->assertEquals(PaymentStatus::list()[PaymentStatus::PART], __('translation.order.payment.part'));
        $this->assertEquals(PaymentStatus::list()[PaymentStatus::FULL], __('translation.order.payment.full'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_ok()
    {
        PaymentStatus::assert(PaymentStatus::NONE);
    }

    /** @test */
    public function assert_fail()
    {
        $wrongState = 'wrong';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('error.not valid order payment status', ['status' => $wrongState]));

        PaymentStatus::assert($wrongState);
    }

    /** @test */
    public function check_ok()
    {
        $this->assertTrue(PaymentStatus::check(PaymentStatus::FULL));
    }

    /** @test */
    public function check_fail()
    {
        $this->assertFalse(PaymentStatus::check('wrong'));
    }

    /** @test */
    public function create_success_none()
    {
        $status = PaymentStatus::create(PaymentStatus::NONE);

        $this->assertTrue($status instanceof PaymentStatus);
        $this->assertEquals($status->getValue(), PaymentStatus::NONE);
        $this->assertTrue($status->isNone());
        $this->assertFalse($status->isPart());
    }

    /** @test */
    public function create_success_not()
    {
        $status = PaymentStatus::create(PaymentStatus::NOT);

        $this->assertTrue($status instanceof PaymentStatus);
        $this->assertEquals($status->getValue(), PaymentStatus::NOT);
        $this->assertTrue($status->isNot());
        $this->assertFalse($status->isPart());
    }

    /** @test */
    public function create_success_part()
    {
        $status = PaymentStatus::create(PaymentStatus::PART);

        $this->assertTrue($status instanceof PaymentStatus);
        $this->assertEquals($status->getValue(), PaymentStatus::PART);
        $this->assertTrue($status->isPart());
        $this->assertFalse($status->isNot());
    }

    /** @test */
    public function create_success_full()
    {
        $status = PaymentStatus::create(PaymentStatus::FULL);

        $this->assertTrue($status instanceof PaymentStatus);
        $this->assertEquals($status->getValue(), PaymentStatus::FULL);
        $this->assertTrue($status->isFull());
        $this->assertFalse($status->isPart());
    }

    /** @test */
    public function create_fail()
    {
        $wrongState = 'wrong';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('error.not valid order payment status', ['status' => $wrongState]));

        PaymentStatus::create($wrongState);
    }
}

