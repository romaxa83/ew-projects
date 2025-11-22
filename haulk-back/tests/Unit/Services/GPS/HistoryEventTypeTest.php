<?php

namespace Tests\Unit\Services\GPS;

use App\Models\GPS\History;
use App\Models\GPS\Message;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\GPS\GPSDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class HistoryEventTypeTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    protected GPSDataService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(GPSDataService::class);
    }

    public function test_driving_event(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Truck $truck */
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => true,
            'idling' => false,
            'engine_off' => false,
        ]);

        $this->service->processMessages();
        $this->assertDatabaseHas(
            History::TABLE_NAME,
            [
                'truck_id' => $truck->id,
                'event_type' => History::EVENT_DRIVING,
            ],
            'pgsql_gps'
        );

        $truck->refresh();
        $this->assertNotEmpty($truck->last_driving_at);
    }

    public function test_engine_off_event(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Truck $truck */
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => false,
            'idling' => false,
            'engine_off' => true,
        ]);

        $this->service->processMessages();
        $this->assertDatabaseHas(
            History::TABLE_NAME,
            [
                'truck_id' => $truck->id,
                'event_type' => History::EVENT_ENGINE_OFF,
            ],
            'pgsql_gps'
        );
    }

    public function test_engine_off_event_for_trailer(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Trailer $trailer */
        $trailer = factory(Trailer::class)
            ->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => false,
            'idling' => false,
            'engine_off' => true,
        ]);

        $this->service->processMessages();
        $this->assertDatabaseHas(
            History::TABLE_NAME,
            [
                'trailer_id' => $trailer->id,
                'event_type' => History::EVENT_TRAILER_STOPPED,
            ],
            'pgsql_gps'
        );
    }

    public function test_engine_idle_event(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Truck $truck */
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => false,
            'idling' => true,
            'engine_off' => false,
        ]);

        $this->service->processMessages();
        $this->assertDatabaseHas(
            History::TABLE_NAME,
            [
                'truck_id' => $truck->id,
                'event_type' => History::EVENT_IDLE,
            ],
            'pgsql_gps'
        );
    }

    public function test_engine_idle_event_for_trailer(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Trailer $trailer */
        $trailer = factory(Trailer::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => false,
            'idling' => true,
            'engine_off' => false,
        ]);

        $this->service->processMessages();
        $this->assertDatabaseHas(
            History::TABLE_NAME,
            [
                'trailer_id' => $trailer->id,
                'event_type' => History::EVENT_TRAILER_STOPPED,
            ],
            'pgsql_gps'
        );
    }

    public function test_engine_idle_without_idle_status_event(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Truck $truck */
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => false,
            'idling' => null,
            'engine_off' => false,
            'speed' => 0,
        ]);

        $this->service->processMessages();
        $this->assertDatabaseHas(
            History::TABLE_NAME,
            [
                'truck_id' => $truck->id,
                'event_type' => History::EVENT_IDLE,
            ],
            'pgsql_gps'
        );
    }

    public function test_engine_long_idle_event(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Truck $truck */
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        $history = History::factory()->create([
            'truck_id' => $truck->id,
            'received_at' => now()->subSeconds(config('gps.long_idle_min_duration') + 60),
            'event_type' => History::EVENT_IDLE,
        ]);
        $truck->last_gps_history_id = $history->id;
        $truck->save();

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => false,
            'idling' => true,
            'engine_off' => false,
        ]);

        $this->service->processMessages();
        $this->assertDatabaseHas(
            History::TABLE_NAME,
            [
                'truck_id' => $truck->id,
                'event_type' => History::EVENT_LONG_IDLE,
            ],
            'pgsql_gps'
        );
    }

    public function test_engine_long_idle_event_for_trailer(): void
    {
        $driver = $this->driverFactory();
        /** @var Device $device */
        $device = Device::factory()->create(['imei' => 'test_imei']);
        /** @var Trailer $trailer */
        $trailer = factory(Trailer::class)->create(['gps_device_id' => $device->id, 'driver_id' => $driver->id]);

        $history = History::factory()->create([
            'trailer_id' => $trailer->id,
            'received_at' => now()->subSeconds(config('gps.long_idle_min_duration') + 60),
            'event_type' => History::EVENT_IDLE,
        ]);
        $trailer->last_gps_history_id = $history->id;
        $trailer->save();

        Message::factory()->create([
            'imei' => $device->imei,
            'driving' => false,
            'idling' => null,
            'engine_off' => false,
            'speed' => 0,
        ]);

        $this->service->processMessages();
        $this->assertDatabaseHas(
            History::TABLE_NAME,
            [
                'trailer_id' => $trailer->id,
                'event_type' => History::EVENT_TRAILER_STOPPED,
            ],
            'pgsql_gps'
        );
    }
}
