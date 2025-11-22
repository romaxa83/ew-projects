<?php

namespace Database\Factories\Verify;

use App\Models\Verify\SmsVerify;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmsVerifyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmsVerify::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'phone' => new Phone($this->faker->phoneNumber),
        ];
    }
}

