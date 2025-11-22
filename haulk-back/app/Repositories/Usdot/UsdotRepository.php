<?php

namespace App\Repositories\Usdot;

interface UsdotRepository
{
    public function fetchCarrierByUsdot(int $usdot);

    public function fetchAuthorityByUsdot(int $usdot);

    public function fetchDocketNumbersByUsdot(int $usdot);
}
