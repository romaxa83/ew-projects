<?php

namespace Tests\Unit\Services\Security;

use App\Models\Security\IpAccess;
use App\Services\Security\IpAccessService;
use Core\ValueObjects\IpAddressValueObject;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class IpAccessServiceTest extends TestCase
{
    private IpAccessService $service;

    public function test_check_ip_address_in_access_list_success(): void
    {
        $fromEnv1 = '10.124.0.1';
        $fromEnv2 = '172.16.0.1';
        Config::set('security.ip-access.list', [$fromEnv1, $fromEnv2]);

        $ipAddress1 = '192.168.0.1';
        $ipAddress2 = '192.168.0.2';
        $ipAddress3 = '192.168.0.3';
        $ipAddress5 = '192.168.0.5';
        IpAccess::factory()->create(['address' => $ipAddress1]);
        IpAccess::factory()->create(['address' => $ipAddress2]);
        IpAccess::factory()->create(['address' => $ipAddress3]);
        IpAccess::factory()->disabled()->create(['address' => $ipAddress5]);

        self::assertTrue(
            $this->service->check(new IpAddressValueObject($ipAddress1))
        );

        self::assertTrue(
            $this->service->check(new IpAddressValueObject($fromEnv1))
        );

        self::assertTrue(
            $this->service->check(new IpAddressValueObject($fromEnv2))
        );

        self::assertFalse(
            $this->service->check(new IpAddressValueObject('192.168.0.4'))
        );

        self::assertFalse(
            $this->service->check(new IpAddressValueObject($ipAddress5))
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(IpAccessService::class);
    }
}
