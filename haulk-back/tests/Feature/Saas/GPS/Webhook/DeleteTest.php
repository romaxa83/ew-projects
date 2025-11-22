<?php

namespace Tests\Feature\Saas\GPS\Webhook;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\GPS\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        Config::set('FLESPI_WEBHOOK_AUTH_TOKEN', 'test');

        $data = [
            'data' => [
                "origin_id" => 5179239,
            ],
        ];

        /** @var $model Device */
        $model = $this->deviceBuilder->flespiDeviceId(data_get($data,'data.origin_id'))->create();

        $this->deleteJson(route('v1.saas.flespi-webhook.delete'), $data, [
            'Authorization' => 'W5OkibwwijOIirILNezjDsMdtD62o3EaZhi7gErmL3VTkrtVuS4X8EYr7VkjOMiD'
        ])
        ;

        $this->assertFalse(Device::query()->where('flespi_device_id', data_get($data,'data.origin_id'))->exists());

        $model = Device::query()->withTrashed()->where('flespi_device_id', data_get($data,'data.origin_id'))->first();
        $this->assertEquals($model->status, DeviceStatus::DELETED);
    }
}

