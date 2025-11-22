<?php

namespace Database\Factories\TypeOfWorks;

use App\Models\TypeOfWorks\TypeOfWork;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypeOfWorks\TypeOfWork>
 */
class TypeOfWorkFactory extends BaseFactory
{
    protected $model = TypeOfWork::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->title,
            'duration' => '3:00',
            'hourly_rate' => 10.9,
        ];
    }
}

