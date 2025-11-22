<?php

namespace Database\Factories\Payments;

use App\Enums\Payments\PaymentReturnPlatformEnum;
use App\Enums\Payments\PayPalCheckoutStatusEnum;
use App\Models\Payments\PayPalCheckout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|PayPalCheckout[]|PayPalCheckout create(array $attributes = [])
 */
class PayPalCheckoutFactory extends Factory
{
    protected $model = PayPalCheckout::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->lexify,
            'return_platform' => PaymentReturnPlatformEnum::WEB,
            'checkout_status' => PayPalCheckoutStatusEnum::CREATED,
            'approve_url' => $this->faker->url,
            'created_at' => time()
        ];
    }
}
