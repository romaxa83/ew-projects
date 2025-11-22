<?php

namespace Api\Users\Users;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserShortListTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_show_all_users_for_super_admin(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->driverFactory(['first_name' => 'testDriver1']);
        $this->driverFactory();
        $this->driverOwnerFactory(['first_name' => 'testDriver2']);
        $this->dispatcherFactory(['first_name' => 'testDriver']);

        $driverRole = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);
        $driverOwnerRole = $this->getRoleRepository()->findByName(User::OWNER_DRIVER_ROLE);
        $filter = [
            'q' => 'testDriver',
            'roles' => [$driverRole->id, $driverOwnerRole->id],
        ];

        $response = $this->getJson(route('users.shortlist', $filter))
            ->assertOk();

        $this->assertCount(2, $response['data']);
    }

    public function test_it_search_by_id(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver = $this->driverFactory();
        $this->driverFactory();
        $this->driverOwnerFactory();
        $this->dispatcherFactory();

        $filter = [
            'searchid' => $driver->id,
        ];

        $response = $this->getJson(route('users.shortlist', $filter))
            ->assertOk();

        $this->assertCount(1, $response['data']);
    }
}
