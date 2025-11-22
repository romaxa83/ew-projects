<?php

namespace Tests\Helpers\Traits;

use App\Models\Users\DriverInfo;
use App\Models\Users\User;

trait DriverFactoryHelper
{

    use UserFactoryHelper;

    protected function getDriver(User $dispatcher): User
    {
        return $this->driverFactory([
            'first_name' => 'Driver ',
            'last_name' => $this->faker->name,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->email,
            'owner_id' => $dispatcher->id
        ]);
    }

    protected function getDriverInfo(User $driver): DriverInfo
    {
        return DriverInfo::factory()->create([
            'driver_id' => $driver->id,
        ]);
    }
}
