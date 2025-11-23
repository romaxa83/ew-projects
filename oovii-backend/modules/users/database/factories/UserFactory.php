<?php

namespace WezomCms\Users\Database\Factories;

use Faker\Provider\Person;
use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\Users\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement([Person::GENDER_FEMALE, Person::GENDER_MALE]);

        return [
            'name' => $this->faker->firstName($gender),
            'surname' => $this->faker->lastName,
//            'patronymic' => $this->faker->middleName($gender),
            'phone' => $this->faker->unique()->numerify('+380#########'),
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'registered_through' => User::EMAIL,
            'password' => '$2y$10$LZXHvrXR0og9jG8puzZlUudlnrDwzh3k57HwpAlf1Bh46bvM3OhJe', // password
            'active' => $this->faker->boolean,
            'bonus' => $this->faker->numberBetween(100, 500),
            'lang' => 'kk',
        ];
    }

    public function unverified(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
