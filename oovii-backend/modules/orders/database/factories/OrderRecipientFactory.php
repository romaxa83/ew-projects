<?php

namespace WezomCms\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Orders\Models\OrderRecipient;

class OrderRecipientFactory extends Factory
{
    protected $model = OrderRecipient::class;

    public function definition(): array
    {
        return [
            'recipient_is_me' => false,
            'name' => $this->faker->firstName,
            'surname' => $this->faker->lastName,
            'phone' => $this->faker->numerify('+76#########'),
            'comment' => $this->faker->sentence,
        ];
    }
}
