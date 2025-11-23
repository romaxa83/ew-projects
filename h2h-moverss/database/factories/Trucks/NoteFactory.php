<?php

namespace Database\Factories\Trucks;

use App\Models\Truck\Notes;
use App\Models\Truck\Truck;
use App\User;
use Database\Factories\BaseFactory;

class NoteFactory extends BaseFactory
{
    protected $model = Notes::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'truck_id' => Truck::factory(),
            'user_id' => User::factory(),
            'value' => $this->faker->sentence(),
            'deleted_at' => null,
        ];
    }
}
