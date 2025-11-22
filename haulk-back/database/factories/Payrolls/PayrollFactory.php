<?php

namespace Database\Factories\Payrolls;

use App\Models\Payrolls\Payroll;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method Payroll|Payroll[]|Collection create($attributes = [], ?Model $parent = null)
 */
class PayrollFactory extends Factory
{

    protected $model = Payroll::class;

    public function definition(): array
    {
        $driver = User::factory()->create();
        $driver->assignRole(User::DRIVER_ROLE);
        return [
            'carrier_id' => 1,
            'driver_rate' => 50,
            'driver_id' => $driver->id,
        ];
    }
}
