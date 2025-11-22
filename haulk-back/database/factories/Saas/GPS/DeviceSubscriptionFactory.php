<?php

namespace Database\Factories\Saas\GPS;

use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceSubscriptionFactory extends Factory
{
    protected $model = DeviceSubscription::class;

    public function definition(): array
    {
        return [
            'status' => DeviceSubscriptionStatus::DRAFT(),
            'company_id' => Company::factory(),
            'activate_at' => null,
            'activate_till_at' => null,
            'canceled_at' => null,
            'access_till_at' => null,
            'send_warning_notify' => false,
            'current_rate' => config('billing.gps.price'),
            'next_rate' => null,
        ];
    }
}
