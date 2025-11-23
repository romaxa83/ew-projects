<?php

namespace Database\Factories\Tasks;

use App\Models\Tasks\Status;
use App\Models\Tasks\Task;
use App\Models\Tasks\Type;
use App\User;
use Database\Factories\BaseFactory;

class TaskFactory extends BaseFactory
{
    protected $model = Task::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'type_id' => Type::factory(),
            'status_id' => Status::factory(),
            'division_id' => 1,
            'title' => $this->faker->city(),
            'description' => $this->faker->sentence(),
            'result' => null,
            'priority' => 1,
            'due_date' => null,
            'user_id' => User::factory(),
            'executor_id' => null,
            'order_id' => null,
            'notify_holder' => null,
            'notify_subscribers' => null,
            'miscs' => [],
            'deleted_at' => null,
            'result_at' => null,
        ];
    }
}
