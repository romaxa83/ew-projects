<?php

namespace Database\Factories\Verify;

use App\Models\Verify\EmailVerify;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailVerifyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailVerify::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email_token' => 'some_token',
        ];
    }
}
