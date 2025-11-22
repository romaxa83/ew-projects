<?php

namespace Database\Factories\User;

use App\Models\User\User;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'password' =>  \Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone' => new Phone($this->faker->phoneNumber),
            'lang' => app('localization')->getDefaultSlug(),
            'salt' => Str::random(20),
            'device_id' => Str::random(30),
            'uuid' => null,
//            'has_new_notifications' => false
        ];
    }

//    /**
//     * Indicate that the model's email address should be unverified.
//     *
//     * @return \Illuminate\Database\Eloquent\Factories\Factory
//     */
//    public function unverified()
//    {
//        return $this->state(function (array $attributes) {
//            return [
//                'email_verified_at' => null,
//            ];
//        });
//    }
}
