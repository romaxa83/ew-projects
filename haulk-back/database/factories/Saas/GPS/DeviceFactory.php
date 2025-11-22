<?php

namespace Database\Factories\Saas\GPS;

use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Models\Saas\GPS\Device;
use App\ValueObjects\Phone;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'status' => DeviceStatus::ACTIVE,
            'phone' => new Phone($this->faker->unique()->phoneNumber),
            'name' => $this->faker->bothify('#####????###'),
            'imei' => $this->faker->bothify('#####????###'),
            'active_at' => null,
            'inactive_at' => null,
            'company_device_name' => null,
            'status_activate_request' => DeviceStatusActivateRequest::NONE(),
            'status_request' => DeviceRequestStatus::NONE(),
            'request_closed_at' => null,
            'active_till_at' => null,
            'send_request_user_id' => null,
            'is_connected' => false,
        ];
    }
}
