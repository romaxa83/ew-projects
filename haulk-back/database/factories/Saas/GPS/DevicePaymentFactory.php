<?php

namespace Database\Factories\Saas\GPS;

use App\Enums\Format\DateTimeEnum;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DevicePayment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class DevicePaymentFactory extends Factory
{
    protected $model = DevicePayment::class;

    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'company_id' => Company::factory(),
            'deactivate' => false,
            'amount' => config('billing.gps.price'),
            'date' => CarbonImmutable::now()->format(DateTimeEnum::DATE),
        ];
    }
}
