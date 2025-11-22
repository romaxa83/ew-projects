<?php

namespace Tests\Unit\Models\User;

use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class UserTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    /** @test */
    public function user_have_new_phone()
    {
        $user = $this->userBuilder()->setNewPhone('389666666666')->create();

        $this->assertTrue($user->haveNewPhone());
    }

    /** @test */
    public function user_not_have_new_phone()
    {
        $user = $this->userBuilder()->create();
        $user->refresh();

        $this->assertFalse($user->haveNewPhone());
    }

    /** @test */
    public function user_list_status()
    {
        $this->assertNotEmpty(User::statusList());
        $this->assertIsArray(User::statusList());
        $this->assertCount(3, User::statusList());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function user_status_assert_ok()
    {
        User::assertStatus(User::VERIFY);
    }

    /** @test */
    public function user_status_assert_fail()
    {
        $this->expectException(\InvalidArgumentException::class);

        User::assertStatus('wrong');
    }

    /** @test */
    public function user_status_check_ok()
    {
        $this->assertTrue(User::checkStatus(User::VERIFY));
    }

    /** @test */
    public function user_status_check_fail()
    {
        $this->assertFalse(User::checkStatus('wrong'));
    }

    /** @test */
    public function user_is_draft()
    {
        $user = $this->userBuilder()->setStatus(User::DRAFT)->create();
        $user->refresh();

        $this->assertTrue($user->isDraft());
        $this->assertFalse($user->isVerify());
        $this->assertFalse($user->isActive());
    }

    /** @test */
    public function user_is_active()
    {
        $user = $this->userBuilder()->setStatus(User::ACTIVE)->create();
        $user->refresh();

        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isDraft());
        $this->assertFalse($user->isVerify());
    }

    /** @test */
    public function user_is_verify()
    {
        $user = $this->userBuilder()->setStatus(User::VERIFY)->create();
        $user->refresh();

        $this->assertTrue($user->isVerify());
        $this->assertFalse($user->isActive());
        $this->assertFalse($user->isDraft());
    }
}


