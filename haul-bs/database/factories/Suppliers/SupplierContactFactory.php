<?php

namespace Database\Factories\Suppliers;

use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;
use App\Models\Suppliers\SupplierContact;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Suppliers\Supplier>
 */
class SupplierContactFactory extends BaseFactory
{
    protected $model = SupplierContact::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => new Phone(fake()->unique()->phoneNumber()),
            'phones' => [],
            'phone_extension' => random_int(100, 999),
            'email' => new Email(fake()->unique()->safeEmail()),
            'emails' => [],
            'position' => fake()->name(),
            'is_main' => true,
            'supplier_id' => SupplierFactory::new(),
        ];
    }
}
