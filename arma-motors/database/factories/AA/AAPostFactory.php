<?php

namespace Database\Factories\AA;

use App\Models\AA\AAPost;
use App\Models\Dealership\Dealership;
use Illuminate\Database\Eloquent\Factories\Factory;

class AAPostFactory extends Factory
{
    protected $model = AAPost::class;

    public function definition(): array
    {
        $dealership = Dealership::find(1);
        $alias = $dealership ? $dealership->alias : 'test';

        return [
            'uuid' => $this->faker->uuid,
            'name' => $this->faker->sentence,
            'alias' => $alias
        ];
    }
}

