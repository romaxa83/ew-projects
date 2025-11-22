<?php

namespace Tests\Unit\Types;

use App\Traits\AssetData;
use App\Types\Communication;
use Tests\TestCase;

class CommunicationTest extends TestCase
{
    use AssetData;

    /** @test */
    public function get_list()
    {
        $this->assertNotEmpty(Communication::list());
        $this->assertIsArray(Communication::list());
        $this->assertCount(3, Communication::list());

        $this->assertEquals(Communication::list()[Communication::TELEGRAM], __('translation.communication.telegram'));
        $this->assertEquals(Communication::list()[Communication::VIBER], __('translation.communication.viber'));
        $this->assertEquals(Communication::list()[Communication::PHONE], __('translation.communication.phone'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_ok()
    {
        Communication::assert(Communication::PHONE);
    }

    /** @test */
    public function assert_fail()
    {
        $wrongState = 'wrong';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('error.not valid communication type', ['status' => $wrongState]));

        Communication::assert($wrongState);
    }

    /** @test */
    public function check_ok()
    {
        $this->assertTrue(Communication::check(Communication::PHONE));
    }

    /** @test */
    public function check_fail()
    {
        $this->assertFalse(Communication::check('wrong'));
    }
}


