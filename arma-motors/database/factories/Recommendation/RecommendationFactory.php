<?php

namespace Database\Factories\Recommendation;

use App\Models\Recommendation\Recommendation;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

class RecommendationFactory extends Factory
{
    protected $model = Recommendation::class;

    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4(),
            'user_id' => User::factory(),
            'car_uuid' => Uuid::uuid4(),
            'order_uuid' => Uuid::uuid4(),
            'qty' => 1,
            'text' => $this->faker->sentence,
            'comment' => $this->faker->sentence,
            'rejection_reason' => $this->faker->sentence,
            'author' => $this->faker->name,
            'executor' => $this->faker->name,
            'completed' => true,
            'data' => json_encode([]),
            'completion_at' => null,
            'relevance_at' => null,
        ];
    }
}

