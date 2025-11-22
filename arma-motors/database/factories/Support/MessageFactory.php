<?php

namespace Database\Factories\Support;

use App\Models\Support\Category;
use App\Models\Support\Message;
use App\ValueObjects\Email;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status' => Message::STATUS_DRAFT,
            'email' => new Email($this->faker->unique()->safeEmail),
            'text' => $this->faker->paragraph,
        ];
    }
}
