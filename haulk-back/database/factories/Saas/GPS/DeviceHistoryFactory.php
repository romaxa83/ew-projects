<?php

namespace Database\Factories\Saas\GPS;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceHistoryFactory extends Factory
{
    protected $model = DeviceHistory::class;

    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'context' => DeviceHistoryContext::ACTIVATE,
            'changed_data' => [],
        ];
    }
}


