<?php

namespace Database\Factories\Auth;

use App\Models\Auth\MemberPhoneVerification;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|MemberPhoneVerification[]|MemberPhoneVerification create(array $attributes = [])
 */
class MemberPhoneVerificationFactory extends Factory
{
    protected $model = MemberPhoneVerification::class;

    public function definition(): array
    {
        return [
            'phone' => new Phone($this->faker->e164PhoneNumber),
            'code' => $this->faker->randomNumber(),
            'sms_token' => (string)$this->faker->unique()->randomNumber(),
            'sms_token_expires_at' => now()->addSeconds(config('auth.sms.token_lifetime')),
            'access_token' => null,
            'access_token_expires_at' => null,
        ];
    }

    public function withAccessToken(): self
    {
        return $this->state(
            [
                'access_token' => (string)$this->faker->unique()->randomNumber(),
                'access_token_expires_at' => now()->addSeconds(config('auth.sms.access_token_lifetime')),
            ]
        );
    }
}
