<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trailers;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use Tests\Feature\Api\Vehicles\VehicleDeleteAttachmentTest;

class TrailerDeleteAttachmentTest extends VehicleDeleteAttachmentTest
{
    protected string $routeName = 'body-shop.trailers.delete-attachment';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Trailer::class)->create($attributes + ['carrier_id' => null]);
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsBodyShopSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }
}
