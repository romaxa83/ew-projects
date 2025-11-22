<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use Tests\Feature\Api\Vehicles\VehicleDestroyTest;
use App\Models\Vehicles\Vehicle;

class TrailerDestroyTest extends VehicleDestroyTest
{
    protected string $routeName = 'trailers.destroy';

    protected string $tableName = Trailer::TABLE_NAME;
    protected string $orderColumnName = 'trailer_id';

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
