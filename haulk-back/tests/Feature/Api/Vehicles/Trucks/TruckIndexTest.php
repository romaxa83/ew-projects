<?php

namespace Tests\Feature\Api\Vehicles\Trucks;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\TruckComment;
use App\Models\Vehicles\Truck;
use Tests\Feature\Api\Vehicles\VehicleIndexTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TruckIndexTest extends VehicleIndexTest
{
    use UserFactoryHelper;

    protected string $routeName = 'trucks.index';

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsBodyShopAdmin();
    }

    public function test_it_show_for_accountant(): void
    {
        $this->loginAsCarrierAccountant();
        $this->getJson(route($this->routeName))
            ->assertOk();
    }

    public function test_it_show_for_dispatcher(): void
    {
        $this->loginAsCarrierDispatcher();
        $this->getJson(route($this->routeName))
            ->assertOk();
    }

    public function test_comments_count(): void
    {
        $truck = factory(Truck::class)->create();

        factory(TruckComment::class)->create([
            'truck_id' => $truck->id,
            'user_id' => $this->bsAdminFactory()->id,
        ]);
        factory(TruckComment::class)->create([
            'truck_id' => $truck->id,
            'user_id' => $this->dispatcherFactory()->id,
        ]);
        factory(TruckComment::class)->create([
            'truck_id' => $truck->id,
            'user_id' => $this->dispatcherFactory()->id,
        ]);

        $this->loginAsPermittedUser();

        $response = $this->getJson(route($this->routeName))
            ->assertOk();

        $this->assertEquals(2, $response['data'][0]['comments_count']);
    }
}
