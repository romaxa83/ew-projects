<?php

namespace Tests\Unit\Types;

use App\Traits\AssetData;
use App\Types\UserType;
use Tests\TestCase;

class UserTypeTest extends TestCase
{
    use AssetData;

    /** @test */
    public function get_list()
    {
        $this->assertNotEmpty(UserType::list());
        $this->assertIsArray(UserType::list());
        $this->assertCount(2, UserType::list());

        $this->assertEquals(UserType::list()[UserType::TYPE_PERSONAL], __('translation.user.type.personal'));
        $this->assertEquals(UserType::list()[UserType::TYPE_LEGAL], __('translation.user.type.legal'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_ok()
    {
        UserType::assert(UserType::TYPE_LEGAL);
    }

    /** @test */
    public function assert_fail()
    {
        $wrongState = 'wrong';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('error.not valid user type', ['status' => $wrongState]));

        UserType::assert($wrongState);
    }

    /** @test */
    public function check_ok()
    {
        $this->assertTrue(UserType::check(UserType::TYPE_LEGAL));
    }

    /** @test */
    public function check_fail()
    {
        $this->assertFalse(UserType::check('wrong'));
    }
}
