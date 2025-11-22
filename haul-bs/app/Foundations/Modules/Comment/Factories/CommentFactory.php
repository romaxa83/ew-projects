<?php

namespace App\Foundations\Modules\Comment\Factories;

use App\Foundations\Modules\Comment\Models\Comment;
use App\Models\Customers\Customer;
use App\Models\Users\User;
use Database\Factories\BaseFactory;

class CommentFactory extends BaseFactory
{
    protected $model = Comment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $model = Customer::factory()->create();

        return [
            'model_id' => $model->id,
            'model_type' => $model::class,
            'author_id' => User::factory(),
            'text' => fake()->sentence,
            'timezone' => null,
        ];
    }
}
