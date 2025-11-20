<?php

namespace Database\Factories\Calls;

use App\Enums\Calls\QueueStatus;
use App\Enums\Calls\QueueType;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Queue[]|Queue create(array $attributes = [])
 */
class QueueFactory extends BaseFactory
{
    protected $model = Queue::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'employee_id' => null,
            'status' => QueueStatus::WAIT(),
            'caller_num' => $this->faker->phoneNumber,
            'caller_name' => $this->faker->name,
            'connected_num' => $this->faker->phoneNumber,
            'connected_name' => $this->faker->name,
            'position' => 44,
            'wait' => 44,
            'serial_number' => $this->faker->uuid,
            'case_id' => $this->faker->postcode,
            'comment' => $this->faker->sentence,
            'uniqueid' => $this->faker->uuid,
            'channel' => $this->faker->word,
            'connected_at' => null,
            'in_call' => 0,
            'type' => QueueType::QUEUE,
        ];
    }
}
