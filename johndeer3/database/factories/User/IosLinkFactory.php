<?php

namespace Database\Factories\User;

use App\Models\User\IosLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class IosLinkFactory extends Factory
{
    protected $model = IosLink::class;

    public function definition(): array
    {
        $code = $this->faker->postcode;
        return [
            'code' => $code,
            'link' => "https://apps.apple.com/redeem?code={$code}&ctx=apps",
            'status' => true,
        ];
    }
}

