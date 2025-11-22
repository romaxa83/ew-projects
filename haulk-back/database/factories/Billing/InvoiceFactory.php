<?php
namespace Database\Factories\Billing;

use App\Models\Billing\Invoice;
use App\Models\Saas\Company\Company;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'carrier_id' => Company::factory(),
            'created_at' => now(),
            'updated_at' => now(),
            'billing_start' => CarbonImmutable::now()->subMonth()->format('Y-m-d'),
            'billing_end' => CarbonImmutable::now()->subMonth()->format('Y-m-d'),
            'amount' =>  $this->faker->randomFloat(2, 0, pow(10, 4)),
            'pending' => false,
            'trans_id' => null,
            'is_paid' => $this->faker->boolean,
            'public_token' => $this->faker->md5,
            'company_name' => $this->faker->company,
            'has_gps_subscription' => false,
            'gps_device_data' => [],
            'gps_device_payment_data' => [],
            'gps_device_amount' => $this->faker->randomFloat(2, 0, pow(10, 4)),
            'drivers_amount' => $this->faker->randomFloat(2, 0, pow(10, 4)),
        ];
    }
}
