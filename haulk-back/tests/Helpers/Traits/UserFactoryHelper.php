<?php

namespace Tests\Helpers\Traits;

use App\Models\Users\User;

trait UserFactoryHelper
{
    public function userFactory(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    public function dispatcherFactory(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole(User::DISPATCHER_ROLE);

        return $user;
    }

    public function driverFactory(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole(User::DRIVER_ROLE);

        return $user;
    }

    public function bsSuperAdminFactory(array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['carrier_id' => null]);
        $user->assignRole(User::BSSUPERADMIN_ROLE);

        return $user;
    }

    public function bsAdminFactory(array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['carrier_id' => null]);
        $user->assignRole(User::BSADMIN_ROLE);

        return $user;
    }

    public function bsMechanicFactory(array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['carrier_id' => null]);
        $user->assignRole(User::BSMECHANIC_ROLE);

        return $user;
    }

    public function driverOwnerFactory(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole(User::OWNER_DRIVER_ROLE);

        return $user;
    }

    public function ownerFactory(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole(User::OWNER_ROLE);

        return $user;
    }
}
