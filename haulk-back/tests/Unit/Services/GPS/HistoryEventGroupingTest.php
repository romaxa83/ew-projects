<?php

namespace Tests\Unit\Services\GPS;

use App\Models\GPS\History;
use App\Models\GPS\Message;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Truck;
use App\Services\GPS\GPSDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class HistoryEventGroupingTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    protected GPSDataService $service;

    public function test_engine_off_event_grouping(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Truck $truck */
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        /** @var History $history */
        $history = History::factory()->create([
            'truck_id' => $truck->id,
            'received_at' => now()->subMinutes(3),
            'event_type' => History::EVENT_ENGINE_OFF,
        ]);
        $truck->last_gps_history_id = $history->id;
        $truck->save();

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => false,
            'idling' => false,
            'engine_off' => true,
        ]);

        $this->service->processMessages();
        $this->assertEquals(1, History::all()->count());
        $history->refresh();

        $this->assertNotEmpty($history->event_duration);
    }

    public function test_engine_off_event_not_grouping_on_different_days(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Truck $truck */
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        /** @var History $history */
        $history = History::factory()->create([
            'truck_id' => $truck->id,
            'received_at' => now()->subDay(),
            'event_type' => History::EVENT_ENGINE_OFF,
        ]);
        $truck->last_gps_history_id = $history->id;
        $truck->save();

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => false,
            'idling' => false,
            'engine_off' => true,
        ]);

        $this->service->processMessages();
        $this->assertEquals(2, History::all()->count());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(GPSDataService::class);
    }
}
