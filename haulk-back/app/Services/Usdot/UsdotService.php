<?php

namespace App\Services\Usdot;

use App\Entities\Usdot\CarrierInfo;
use App\Repositories\Usdot\UsdotRepository;
use Illuminate\Cache\CacheManager;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UsdotService
{
    private UsdotRepository $carriers;

    private CacheManager $cache;

    public function __construct(UsdotRepository $carriers)
    {
        $this->carriers = $carriers;

        $this->cache = app('cache');
    }

    public function getCarrierInfoByUsdot(int $usdot): CarrierInfo
    {
        return $this->cache()->rememberForever(
            'carrier_' . $usdot,
            function () use ($usdot) {
                if (is_null($carrier = $this->carriers->fetchCarrierByUsdot($usdot))) {
                    throw new UnprocessableEntityHttpException('Usdot is invalid');
                }

                $docketNumbers = $this->carriers->fetchDocketNumbersByUsdot($usdot);

                return CarrierInfo::byFmcsaCarrierAndAuthority($carrier, $docketNumbers);
            }
        );
    }

    private function cache(): CacheManager
    {
        return $this->cache;
    }
}
