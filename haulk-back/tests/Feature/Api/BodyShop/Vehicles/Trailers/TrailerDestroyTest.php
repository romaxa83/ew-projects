<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trailers;

use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use Illuminate\Http\Response;
use Tests\Feature\Api\Vehicles\VehicleDestroyTest;
use App\Models\Vehicles\Vehicle;

class TrailerDestroyTest extends VehicleDestroyTest
{
    protected string $routeName = 'body-shop.trailers.destroy';

    protected string $tableName = Trailer::TABLE_NAME;
    protected string $orderColumnName = 'trailer_id';

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

    public function test_it_delete_from_company(): void
    {
        $vehicle = factory(Trailer::class)->create();

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());

        $this->loginAsPermittedUser();
        $this->deleteJson(route($this->routeName, $vehicle))
            ->assertForbidden();

        $this->assertDatabaseHas($this->tableName, $vehicle->getAttributes());
    }
}
