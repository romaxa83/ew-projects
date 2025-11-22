<?php

namespace Database\Factories\Saas\GPS;

use App\Enums\Saas\GPS\Request\DeviceRequestSource;
use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceRequestFactory extends Factory
{
    protected $model = DeviceRequest::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'status' => DeviceRequestStatus::NEW(),
            'source' => DeviceRequestSource::CRM(),
            'qty' => 10,
            'closed_at' => null,
            'comment' => null,
        ];
    }
}

