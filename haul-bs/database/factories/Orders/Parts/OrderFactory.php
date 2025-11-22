<?php

namespace Database\Factories\Orders\Parts;

use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentTerms;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Foundations\Entities\Locations\AddressEntity;
use App\Models\Orders\Parts\Order;
use Database\Factories\BaseFactory;
use Database\Factories\Customers\CustomerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders\Parts\Order>
 */
class OrderFactory extends BaseFactory
{
    protected $model = Order::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_number' => date('Ymd-' . fake()->numberBetween(1, 100)),
            'customer_id' => CustomerFactory::new(),
            'sales_manager_id' => null,
            'status' => OrderStatus::New,
            'delivery_address' => AddressEntity::make([
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'company' => $this->faker->company,
                'address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => 'TX',
                'zip' => $this->faker->postcode,
                'phone' => '3458888888',
            ]),
            'billing_address' => AddressEntity::make([
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'company' => $this->faker->company,
                'address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => 'TX',
                'zip' => $this->faker->postcode,
                'phone' => '3458888884',
            ]),
            'is_paid' => false,
            'paid_at' => null,
            'total_amount' => null,
            'paid_amount' => null,
            'debt_amount' => null,
            'deleted_at' => null,
            'payment_method' => PaymentMethod::Cash,
            'payment_terms' => PaymentTerms::Day_15,
            'with_tax_exemption' => false,
            'source' => OrderSource::BS,
            'draft_at' => null,
            'delivered_at' => null,
            'past_due_at' => null,
            'delivery_cost' => 0,
        ];
    }
}
