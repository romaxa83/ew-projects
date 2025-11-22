<?php

namespace Database\Factories\Users;

use App\Models\Users\DriverInfo;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverInfoFactory extends Factory
{
    protected $model = DriverInfo::class;

    public function definition(): array
    {
        return [
            'driver_id' => User::factory(),
        ];
    }
}
