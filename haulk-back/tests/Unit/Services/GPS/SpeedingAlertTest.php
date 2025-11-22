<?php

namespace Tests\Unit\Services\GPS;

use App\Models\GPS\Alert;
use App\Models\GPS\Message;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Truck;
use App\Services\GPS\GPSDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class SpeedingAlertTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    protected GPSDataService $service;

    public function test_speeding_alert(): void
    {
        $driver = $this->driverFactory();
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Truck $truck */
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        $this->createLogWithoutSpeeding($truck->gpsDevice->imei);
        $this->service->processMessages();
        $this->assertEquals(0, $this->getSpeedingAlertsCount($truck->id));

        $this->createLogWithoutSpeeding($truck->gpsDevice->imei);
        $this->service->processMessages();
        $this->assertEquals(0, $this->getSpeedingAlertsCount($truck->id));

        $this->createLogWithSpeeding($truck->gpsDevice->imei);
        $this->service->processMessages();
        $this->assertEquals(1, $this->getSpeedingAlertsCount($truck->id));
        $this->assertDatabaseHas(
            Alert::TABLE_NAME,
            [
                'truck_id' => $truck->id,
                'trailer_id' => null,
                'driver_id' => $truck->driver->id,
                'latitude' => 49.069782,
                'longitude' => 28.632826,
                'speed' => 100,
                'alert_type' => Alert::ALERT_TYPE_SPEEDING,
                'alert_subtype' => null,
                'company_id' => $truck->getCompanyId(),
            ],
            'pgsql_gps'
        );

        $this->createLogWithSpeeding($truck->gpsDevice->imei);
        $this->service->processMessages();
        $this->assertEquals(1, $this->getSpeedingAlertsCount($truck->id));

        $this->createLogWithoutSpeeding($truck->gpsDevice->imei);
        $this->service->processMessages();
        $this->assertEquals(1, $this->getSpeedingAlertsCount($truck->id));

        $this->createLogWithSpeeding($truck->gpsDevice->imei);
        $this->service->processMessages();
        $this->assertEquals(2, $this->getSpeedingAlertsCount($truck->id));

    }

    protected function createLogWithSpeeding(string $imei): Message
    {
        return Message::factory()->create([
            'imei' => $imei,
            'received_at' => now(),
            'latitude' => 49.069782,
            'longitude' => 28.632826,
            'heading' => 273.61,
            'vehicle_mileage' => 200,
            'speed' => 100,
            'device_battery_level' => 50,
            'device_battery_charging_status' => true,
            'driving' => true,
            'idling' => false,
            'engine_off' => false,
        ]);
    }

    protected function createLogWithoutSpeeding(string $imei): Message
    {
        return Message::factory()->create([
            'imei' => $imei,
            'received_at' => now(),
            'latitude' => 49.069782,
            'longitude' => 28.632826,
            'heading' => 273.61,
            'vehicle_mileage' => 200,
            'speed' => 50,
            'device_battery_level' => 50,
            'device_battery_charging_status' => true,
            'driving' => true,
            'idling' => false,
            'engine_off' => false,
        ]);
    }

    private function getSpeedingAlertsCount(int $truckId): int
    {
        return Alert::query()
            ->where('truck_id', $truckId)
            ->where('alert_type', Alert::ALERT_TYPE_SPEEDING)
            ->count();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(GPSDataService::class);
    }
}
