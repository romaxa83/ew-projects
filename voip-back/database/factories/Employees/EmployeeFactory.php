<?php

namespace Database\Factories\Employees;

use App\Enums\Employees\Status;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Employee[]|Employee create(array $attributes = [])
 */
class EmployeeFactory extends BaseFactory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'guid' => $this->faker->uuid,
            'status' => Status::FREE(),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->safeEmail,
            'password' => '$2y$10$k1wlbzKjC09gx2yMca/e6.Pm2pCLX9cni8eY0eD2RnjOVzLQ7X/xK', // Password123
            'lang' => app('localization')->getDefaultSlug(),
            'department_id' => Department::factory(),
            'sip_id' => null,
            'is_insert_kamailio' => false,
            'is_insert_queue' => false,
        ];
    }
}
