<?php

namespace Database\Factories\Departments;

use App\Models\Departments\Department;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Department[]|Department create(array $attributes = [])
 */
class DepartmentFactory extends BaseFactory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'guid' => $this->faker->uuid,
            'name' => $this->faker->word,
            'sort' => 0,
            'is_insert_asterisk' => false,
            'num' => null,
            'active' => true,
        ];
    }
}
