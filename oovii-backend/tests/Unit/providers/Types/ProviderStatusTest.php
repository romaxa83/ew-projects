<?php

namespace Tests\Unit\providers\Types;

use Tests\TestCase;
use WezomCms\Providers\Types\Exception\ProviderStatusException;
use WezomCms\Providers\Types\ProviderStatus;

class ProviderStatusTest extends TestCase
{
    /** @test */
    public function get_list()
    {
        $this->assertNotEmpty(ProviderStatus::list());
        $this->assertIsArray(ProviderStatus::list());
        $this->assertCount(2, ProviderStatus::list());

        $this->assertEquals(
            ProviderStatus::list()[ProviderStatus::DRAFT],
            __('cms-providers::admin.provider.status.Draft')
        );
        $this->assertEquals(
            ProviderStatus::list()[ProviderStatus::MODERATED],
            __('cms-providers::admin.provider.status.Moderated')
        );
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_ok()
    {
        ProviderStatus::assert(ProviderStatus::DRAFT);
    }

    /** @test */
    public function assert_fail()
    {
        $wrongStatus = 'wrong';
        $this->expectException(ProviderStatusException::class);
        $this->expectExceptionMessage(
            __('cms-providers::admin.exception.Invalid provider status', [
                'status' => $wrongStatus
            ])
        );

        ProviderStatus::assert($wrongStatus);
    }

    /** @test */
    public function check_ok()
    {
        $this->assertTrue(ProviderStatus::check(ProviderStatus::DRAFT));
    }

    /** @test */
    public function check_fail()
    {
        $this->assertFalse(ProviderStatus::check('wrong'));
    }

    /** @test */
    public function create_success_draft()
    {
        $status = ProviderStatus::create(ProviderStatus::DRAFT);

        $this->assertTrue($status instanceof ProviderStatus);
        $this->assertEquals($status->getValue(), ProviderStatus::DRAFT);
        $this->assertTrue($status->isDraft());
        $this->assertFalse($status->isModerate());
    }

    /** @test */
    public function create_success_moderate()
    {
        $status = ProviderStatus::create(ProviderStatus::MODERATED);

        $this->assertTrue($status instanceof ProviderStatus);
        $this->assertEquals($status->getValue(), ProviderStatus::MODERATED);
        $this->assertFalse($status->isDraft());
        $this->assertTrue($status->isModerate());
    }

    /** @test */
    public function create_fail()
    {
        $wrongStatus = 22;
        $this->expectException(ProviderStatusException::class);
        $this->expectExceptionMessage(
            __('cms-providers::admin.exception.Invalid provider status', [
                'status' => $wrongStatus
            ])
        );

        ProviderStatus::create($wrongStatus);
    }
}
