<?php

namespace Tests\Unit\Repositories\Usdot;

use App\Repositories\Usdot\UsdotApiRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;

/**
 * @group Usdot
 */
class UsdotRepositoryTest extends TestCase
{
    private const USDOT_CARRIER = 2943611;
    private const USDOT_SHIPPER = 3388000;

    private UsdotApiRepository $repository;

    public function test_it_get_carrier_info_from_fmcsa_api(): void
    {
        if (!config('usdot.api_key')) {
            self::markTestSkipped();
        }

        $data = $this->repository->fetchCarrierByUsdot(self::USDOT_CARRIER);

        $carrier = $data['content']['carrier'];
        self::assertEquals('GIG LOGISTICS INC', $carrier['legalName']);
        self::assertEquals('47630', $carrier['phyZipcode']);
    }

    public function test_it_get_authority_from_fmcsa_api(): void
    {
        if (!config('usdot.api_key')) {
            self::markTestSkipped();
        }

        $data = $this->repository->fetchAuthorityByUsdot(self::USDOT_CARRIER);

        $authority = $data['content'][0]['carrierAuthority'];
        self::assertEquals(995965, $authority['docketNumber']);
        self::assertEquals(2943611, $authority['dotNumber']);
    }

    public function test_it_get_shipper_info_from_fmcsa_api(): void
    {
        if (!config('usdot.api_key')) {
            self::markTestSkipped();
        }

        $data = $this->repository->fetchCarrierByUsdot(self::USDOT_SHIPPER);

        $carrier = $data['content']['carrier'];
        self::assertEquals('AUTO TRANSPORT CHICAGO INC', $carrier['legalName']);
        self::assertEquals('60611', $carrier['phyZipcode']);
    }

    public function test_it_get_authority_shipper_from_fmcsa_api(): void
    {
        if (!config('usdot.api_key')) {
            self::markTestSkipped();
        }

        $data = $this->repository->fetchAuthorityByUsdot(self::USDOT_SHIPPER);

        $authority = $data['content'][0]['carrierAuthority'];
        self::assertEquals(1088436, $authority['docketNumber']);
        self::assertEquals(3388000, $authority['dotNumber']);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(UsdotApiRepository::class);
    }
}
