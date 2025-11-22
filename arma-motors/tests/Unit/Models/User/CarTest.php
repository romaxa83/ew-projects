<?php

namespace Tests\Unit\Models\User;

use App\Models\User\Car;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class CarTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    /** @test */
    public function car_list_status()
    {
        $this->assertNotEmpty(Car::statusList());
        $this->assertIsArray(Car::statusList());
        $this->assertCount(3, Car::statusList());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function car_status_assert_ok()
    {
        Car::assertStatus(Car::DRAFT);
    }

    /** @test */
    public function car_status_assert_fail()
    {
        $this->expectException(\InvalidArgumentException::class);

        Car::assertStatus('wrong');
    }

    /** @test */
    public function car_status_check_ok()
    {
        $this->assertTrue(Car::checkStatus(Car::DRAFT));
    }

    /** @test */
    public function car_status_check_fail()
    {
        $this->assertFalse(Car::checkStatus('wrong'));
    }

    /** @test */
    public function list_delete_reason()
    {
        $this->assertNotEmpty(Car::deleteReasonList());
        $this->assertIsArray(Car::deleteReasonList());
        $this->assertCount(2, Car::deleteReasonList());
    }

    /** @test */
    public function check_true_status_verify()
    {
        $this->assertTrue(Car::statusVerify(Car::VERIFY));
    }

    /** @test */
    public function check_false_status_verify()
    {
        $this->assertFalse(Car::statusVerify(Car::MODERATE));
    }
}


