<?php

namespace Database\Factories\Payments;

use App\Models\Payments\PaymentCard;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|PaymentCard[]|PaymentCard create(array $attributes = [])
 */
class PaymentCardFactory extends Factory
{
    protected $model = PaymentCard::class;

    public function definition(): array
    {
        return [
            'member_id' => User::factory(),
            'member_type' => User::MORPH_NAME,
            'code' => substr($this->faker->creditCardNumber, -4),
            'type' => $this->faker->creditCardType,
            'expiration_date' => $this->faker->creditCardExpirationDateString,
            'default' => false,
            'hash' => $this->faker->uuid
        ];
    }
}

