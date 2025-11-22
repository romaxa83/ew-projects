<?php

namespace Database\Factories\Customers;

use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Enums\Customers\CustomerTaxExemptionType;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @method static CustomerTaxExemption|CustomerTaxExemption[]|Collection create(array $attributes = [])
 */
class CustomerTaxExemptionFactory extends Factory
{
    protected $model = CustomerTaxExemption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'date_active_to' => now()->addMonth(),
            'status' => CustomerTaxExemptionStatus::ACCEPTED,
            'type' => CustomerTaxExemptionType::ECOM,
        ];
    }
}
