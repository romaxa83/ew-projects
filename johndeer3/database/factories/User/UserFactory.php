<?php

namespace Database\Factories\User;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'login' => $this->faker->unique()->city,
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$04$kVAU.Z6yxaiKeBp3YLBUTeAiVK4qGAQsaD6UAI0Z54cYkNyhAXadO',
//            'password' =>  \Hash::make('password'),
            'phone' => $this->faker->unique()->numerify('+380#########'),
            'status' => true,
            'lang' => "en",
            'dealer_id' => null,
        ];
    }
}

