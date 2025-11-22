<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trucks;

use App\Models\Users\User;
use Tests\Feature\Api\Vehicles\VehicleIndexTest;

class TruckIndexTest extends VehicleIndexTest
{
    protected string $routeName = 'body-shop.trucks.index';

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsBodyShopSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }
}
