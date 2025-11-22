<?php

namespace Database\Factories\Customers;

use App\Enums\Customers\CustomerType;
use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\Models\Customers\Customer;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customers\Customer>
 */
class CustomerFactory extends BaseFactory
{
    protected $model = Customer::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'first_name' => fake()->name(),
            'last_name' => fake()->name(),
            'email' => new Email(fake()->unique()->safeEmail()),
            'phone' => new Phone(fake()->unique()->phoneNumber()),
            'phones' => [],
            'phone_extension' => random_int(555, 999),
            'notes' => fake()->text(),
            'from_haulk' => false,
            'type' => CustomerType::BS(),
            'sales_manager_id' => null,
            'has_ecommerce_account' => false,
        ];
    }
}
