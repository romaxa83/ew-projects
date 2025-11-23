<?php

namespace WezomCms\SmsVerify\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use WezomCms\SmsVerify\Models\SmsVerify;
use WezomCms\SmsVerify\Services\Tokenizer;

class SmsVerifyFactory extends Factory
{
    protected $model = SmsVerify::class;

    public function definition(): array
    {
        return [
            'phone' => $this->faker->unique()->numerify('+380#########'),
            'code' => $this->faker->unique()->numerify('####'),
            'sms_token' => Tokenizer::generateSmsToken(),
        ];
    }
}
