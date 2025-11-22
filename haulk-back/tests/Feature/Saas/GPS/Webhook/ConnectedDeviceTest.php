<?php

namespace Tests\Feature\Saas\GPS\Webhook;

use App\Models\GPS\Alert;
use App\Models\GPS\History;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class ConnectedDeviceTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected TruckBuilder $truckBuilder;
    protected HistoryBuilder $historyBuilder;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
    }

    /** @test */
    public function success_connection(): void
    {
        $data = [
            "data" => [
                "payload" => [
                    "close_code" =>2,
                    "duration" => 0,
                    "event_code" => 300,
                    "id" => 5215163,
                    "ident" => "861059063081164",
                    "msgs" => 1,
                    "origin_id" => 5215163,
                    "origin_type" =>11,
                    "recv" =>84,
                    "send" => 5,
                    "source" => "172.59.191.74:11269",
                    "timestamp" => 1699369651.503366,
                    "transport" => "tcp"
                ]
            ]
        ];

        /** @var $model Device */
        $model = $this->deviceBuilder->imei(data_get($data, 'data.payload.ident'))->create();

        /** @var $history History */
        $history = $this->historyBuilder->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->device($model)
            ->lastDeviceHistory($history)->create();

        $this->assertFalse($model->is_connected);

        $this->postJson(route('v1.saas.flespi-webhook.connect-device'), $data, [
            'Authorization' => config('flespi.webhook.auth_token')
        ])
        ;

        $model->refresh();

        $this->assertTrue($model->is_connected);

        $this->assertNotEquals($model->truck->last_gps_history_id, $history->id);
        $this->assertEquals($model->truck->lastGPSHistory->latitude, $history->latitude);
        $this->assertEquals($model->truck->lastGPSHistory->longitude, $history->longitude);
        $this->assertEquals(
            $model->truck->lastGPSHistory->alerts[0]->alert_type,
            Alert::ALERT_DEVICE_CONNECTION_RESTORED
        );
    }

    /** @test */
    public function success_connection_not_change(): void
    {
        $data = [
            "data" => [
                "payload" => [
                    "close_code" =>2,
                    "duration" => 0,
                    "event_code" => 301,
                    "id" => 5215163,
                    "ident" => "861059063081164",
                    "msgs" => 1,
                    "origin_id" => 5215163,
                    "origin_type" =>11,
                    "recv" =>84,
                    "send" => 5,
                    "source" => "172.59.191.74:11269",
                    "timestamp" => 1699369651.503366,
                    "transport" => "tcp"
                ]
            ]
        ];

        /** @var $model Device */
        $model = $this->deviceBuilder->imei(data_get($data, 'data.payload.ident'))->create();

        $this->assertFalse($model->is_connected);

        $this->postJson(route('v1.saas.flespi-webhook.connect-device'), $data, [
            'Authorization' => config('flespi.webhook.auth_token')
        ])
        ;

        $model->refresh();

        $this->assertFalse($model->is_connected);
    }

    /** @test */
    public function success_disconnection(): void
    {
        $data = [
            "data" => [
                "payload" => [
                    "close_code" =>2,
                    "duration" => 0,
                    "event_code" => 301,
                    "id" => 5215163,
                    "ident" => "861059063081164",
                    "msgs" => 1,
                    "origin_id" => 5215163,
                    "origin_type" => 11,
                    "recv" =>84,
                    "send" => 5,
                    "source" => "172.59.191.74:11269",
                    "timestamp" => 1699369651.503366,
                    "transport" => "tcp"
                ]
            ]
        ];

        /** @var $model Device */
        $model = $this->deviceBuilder
            ->imei(data_get($data, 'data.payload.ident'))
            ->setData(['is_connected' => true])
            ->create();

        /** @var $history History */
        $history = $this->historyBuilder->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->device($model)
            ->lastDeviceHistory($history)->create();

        $this->assertTrue($model->is_connected);

        $this->postJson(route('v1.saas.flespi-webhook.connect-device'), $data, [
            'Authorization' => config('flespi.webhook.auth_token')
        ])
        ;

        $model->refresh();

        $this->assertFalse($model->is_connected);

        $this->assertNotEquals($model->truck->last_gps_history_id, $history->id);
        $this->assertEquals($model->truck->lastGPSHistory->latitude, $history->latitude);
        $this->assertEquals($model->truck->lastGPSHistory->longitude, $history->longitude);
        $this->assertEquals(
            $model->truck->lastGPSHistory->alerts[0]->alert_type,
            Alert::ALERT_DEVICE_CONNECTION_LOST
        );
    }
}

