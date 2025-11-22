<?php

namespace Database\Factories\Customers;

use App\Enums\Customers\AddressType;
use App\Foundations\ValueObjects\Phone;
use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customers\Address>
 */
class AddressFactory extends BaseFactory
{
    protected $model = Address::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'type' => AddressType::Delivery(),
            'is_default' => false,
            'from_ecomm' => false,
            'first_name' => fake()->name(),
            'last_name' => fake()->name(),
            'company_name' => fake()->company(),
            'address' => fake()->name(),
            'city' => fake()->name(),
            'state' => fake()->name(),
            'zip' => fake()->name(),
            'phone' => new Phone(fake()->unique()->phoneNumber()),
            'sort' => 1,
        ];
    }
}
