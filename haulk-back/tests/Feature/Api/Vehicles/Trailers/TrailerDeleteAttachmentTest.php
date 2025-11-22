<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use Tests\Feature\Api\Vehicles\VehicleDeleteAttachmentTest;

class TrailerDeleteAttachmentTest extends VehicleDeleteAttachmentTest
{
    protected string $routeName = 'trailers.delete-attachment';

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Trailer::class)->create($attributes);
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsCarrierDispatcher();
    }
}
