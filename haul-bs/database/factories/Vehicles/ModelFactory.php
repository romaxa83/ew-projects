<?php

namespace Database\Factories\Vehicles;

use App\Models\Vehicles\Make;
use App\Models\Vehicles\Model;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicles\Model>
 */
class ModelFactory extends BaseFactory
{
    protected $model = Model::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->title,
            'make_id' => MakeFactory::new(),
        ];
    }
}
