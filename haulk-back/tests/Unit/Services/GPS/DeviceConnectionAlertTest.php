<?php

namespace Tests\Unit\Services\GPS;

use App\Models\GPS\Alert;
use App\Models\GPS\History;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Truck;
use App\Services\GPS\GPSDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DeviceConnectionAlertTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    protected GPSDataService $service;

    public function test_device_connection_alert(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Truck $truck */
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);
        $device->refresh();

        $this->service->checkDeviceConnectionAlert($device);
        $this->assertEquals(0, $this->getDeviceConnectionAlertsCount($truck->id));

        $history = $this->createHistory($truck);
        $device->refresh();
        $this->service->checkDeviceConnectionAlert($device);
        $this->assertEquals(0, $this->getDeviceConnectionAlertsCount($truck->id));

        $history->updated_at = now()->subMinutes(config('gps.device_disconnected_time') + 1);
        $history->save();
        $device->refresh();
        $this->service->checkDeviceConnectionAlert($device);
        $this->assertEquals(1, $this->getDeviceConnectionAlertsCount($truck->id));
        $this->assertDatabaseHas(
            Alert::TABLE_NAME,
            [
                'truck_id' => $truck->id,
                'trailer_id' => null,
                'driver_id' => $truck->driver->id,
                'latitude' => $history->latitude,
                'longitude' => $history->longitude,
                'alert_type' => Alert::ALERT_TYPE_DEVICE_CONNECTION,
                'alert_subtype' => null,
                'company_id' => $truck->getCompanyId(),
            ],
            'pgsql_gps'
        );

        $device->refresh();
        $this->service->checkDeviceConnectionAlert($device);
        $this->assertEquals(1, $this->getDeviceConnectionAlertsCount($truck->id));

        $truck->lastGPSHistory->updated_at = now()->subMinutes(config('gps.device_disconnected_time'));
        $truck->lastGPSHistory->save();
        $device->refresh();
        $this->service->checkDeviceConnectionAlert($device);
        $this->assertEquals(1, $this->getDeviceConnectionAlertsCount($truck->id));
    }

    private function createHistory(Truck $truck): History
    {
        /** @var History $history */
        $history = History::factory()->create([
            'truck_id' => $truck->id,
        ]);
        $truck->last_gps_history_id = $history->id;
        $truck->save();

        return $history;
    }

    private function getDeviceConnectionAlertsCount(int $truckId): int
    {
        return Alert::query()
            ->where('truck_id', $truckId)
            ->where('alert_type', Alert::ALERT_TYPE_DEVICE_CONNECTION)
            ->count();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(GPSDataService::class);
    }
}
