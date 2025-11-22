<?php

namespace Tests\Unit\Services\Usdot;

use App\Services\Usdot\UsdotService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;

/**
 * @group Usdot
 */
class UsdotApiServiceTest extends TestCase
{
    private const USDOT_CARRIER = 2943611;
    private const USDOT_SHIPPER = 3388000;

    private UsdotService $service;

    public function test_company_carrier_info(): void
    {
        $info = $this->service->getCarrierInfoByUsdot(self::USDOT_CARRIER);

        self::assertEquals('LEGAL NAME', $info->getName());
        self::assertEquals('12345', $info->getZip());
        self::assertEquals('STREET 123', $info->getAddress());
        self::assertEquals('EA', $info->getState());
        self::assertEquals('VEGAS', $info->getCity());
        self::assertEquals(123456, $info->getDotNumber());
        self::assertEquals(123456, $info->getMcNumber());
        self::assertEquals('A', $info->getStatus());
    }

    public function test_company_shipper_info(): void
    {
        $info = $this->service->getCarrierInfoByUsdot(self::USDOT_SHIPPER);

        self::assertEquals('LEGAL NAME', $info->getName());
        self::assertEquals('12345', $info->getZip());
        self::assertEquals('STREET 123', $info->getAddress());
        self::assertEquals('EA', $info->getState());
        self::assertEquals('VEGAS', $info->getCity());
        self::assertEquals(123456, $info->getDotNumber());
        self::assertEquals(123456, $info->getMcNumber());
        self::assertEquals('A', $info->getStatus());
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(UsdotService::class);
    }

}
