<?php

namespace Tests\Feature\Saas\GPS\History;

use App\Models\GPS\History;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];
    protected HistoryBuilder $historyBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected UserBuilder $userBuilder;
    protected DeviceBuilder $deviceBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyBuilder = resolve(HistoryBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->deviceBuilder = resolve(DeviceBuilder::class);

        $this->passportInit();
    }

    /** @test */
    public function success_pagination(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();


        $this->getJson(route('v1.saas.gps.history'))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'to' => 3,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_page(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();


        $this->getJson(route('v1.saas.gps.history',[
            'page' => 2
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'from' => null,
                    'last_page' => 1,
                    'to' => null,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_per_page(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();


        $this->getJson(route('v1.saas.gps.history',[
            'per_page' => 2
        ]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 2,
                    'to' => 2,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.gps.history'))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'last_page' => 1,
                    'to' => null,
                    'total' => 0,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_id(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $model History */
        $model = $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();


        $this->getJson(route('v1.saas.gps.history',[
            'id' => $model->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $model->id,
                        'received_at' => $model->received_at->timestamp,
                        'created_at' => $model->created_at->timestamp,
                        'location' => [
                            'lat' => $model->latitude,
                            'lng' => $model->longitude,
                        ],
                        'speed' => $model->speed,
                        'vehicle_mileage' => $model->vehicle_mileage,
                        'heading' => $model->heading,
                        'event_type' => $model->event_type,
                        'event_duration' => $model->event_duration,
                        'device_battery_level' => $model->device_battery_level,
                        'device_battery_charging_status' => $model->device_battery_charging_status,
                        'truck' => [
                            'id' => $model->truck->id,
                            'vin' => $model->truck->vin,
                            'make' => $model->truck->make,
                            'model' => $model->truck->model,
                            'year' => $model->truck->year,
                            'type' => $model->truck->type,
                        ]
                    ]
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'to' => 1,
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_event_type(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->historyBuilder->eventType(History::EVENT_DRIVING)->create();
        $this->historyBuilder->eventType(History::EVENT_DRIVING)->create();
        $this->historyBuilder->eventType(History::EVENT_IDLE)->create();
        $this->historyBuilder->eventType(History::EVENT_ENGINE_OFF)->create();


        $this->getJson(route('v1.saas.gps.history',[
            'event_type' => History::EVENT_DRIVING
        ]))
            ->assertJson([
                'meta' => [
                    'to' => 2,
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_truck_id(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();

        $this->historyBuilder->truck($truck)->create();
        $this->historyBuilder->truck($truck)->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();


        $this->getJson(route('v1.saas.gps.history',[
            'truck_id' => $truck->id
        ]))
            ->assertJson([
                'meta' => [
                    'to' => 2,
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_trailer_id(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->create();

        $this->historyBuilder->trailer($trailer)->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();


        $this->getJson(route('v1.saas.gps.history',[
            'trailer_id' => $trailer->id
        ]))
            ->assertJson([
                'meta' => [
                    'to' => 1,
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_driver_id(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $driver User */
        $driver = $this->userBuilder->asDriver()->create();

        $this->historyBuilder->driver($driver)->create();
        $this->historyBuilder->driver($driver)->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();


        $this->getJson(route('v1.saas.gps.history',[
            'driver_id' => $driver->id
        ]))
            ->assertJson([
                'meta' => [
                    'to' => 2,
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_device_id(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $device Device */
        $device = $this->deviceBuilder->create();

        $this->historyBuilder->device($device)->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();
        $this->historyBuilder->create();


        $this->getJson(route('v1.saas.gps.history',[
            'device_id' => $device->id
        ]))
            ->assertJson([
                'meta' => [
                    'to' => 1,
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_date_from(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();

        $this->historyBuilder->receivedAt($date->subDays(2))->create();
        $this->historyBuilder->receivedAt($date->subDays(3))->create();
        $this->historyBuilder->receivedAt($date->subDays(5))->create();
        $this->historyBuilder->receivedAt($date->subDays(6))->create();


        $this->getJson(route('v1.saas.gps.history',[
            'date_from' => $date->subDays(4)->format('Y-m-d')
        ]))
            ->assertJson([
                'meta' => [
                    'to' => 2,
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_date_to(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();

        $this->historyBuilder->receivedAt($date->subDays(2))->create();
        $this->historyBuilder->receivedAt($date->subDays(3))->create();
        $this->historyBuilder->receivedAt($date->subDays(5))->create();
        $this->historyBuilder->receivedAt($date->subDays(6))->create();


        $this->getJson(route('v1.saas.gps.history',[
            'date_to' => $date->subDays(3)->format('Y-m-d')
        ]))
            ->assertJson([
                'meta' => [
                    'to' => 3,
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_date_from_and_to(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();

        $this->historyBuilder->receivedAt($date->subDays(2))->create();
        $this->historyBuilder->receivedAt($date->subDays(3))->create();
        $this->historyBuilder->receivedAt($date->subDays(5))->create();
        $this->historyBuilder->receivedAt($date->subDays(6))->create();


        $this->getJson(route('v1.saas.gps.history',[
            'date_from' => $date->subDays(7)->format('Y-m-d'),
            'date_to' => $date->subDays(5)->format('Y-m-d'),
        ]))
            ->assertJson([
                'meta' => [
                    'to' => 2,
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_asc(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();

        $m_1 = $this->historyBuilder->receivedAt($date->subDays(2))->create();
        $m_2 = $this->historyBuilder->receivedAt($date->subDays(5))->create();
        $m_3 = $this->historyBuilder->receivedAt($date->subDays(3))->create();
        $m_4 = $this->historyBuilder->receivedAt($date->subDays(6))->create();


        $this->getJson(route('v1.saas.gps.history',[
            'order_type' => 'asc',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_4->id],
                    ['id' => $m_2->id],
                    ['id' => $m_3->id],
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'to' => 4,
                    'total' => 4,
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_desc(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();

        $m_1 = $this->historyBuilder->receivedAt($date->subDays(2))->create();
        $m_2 = $this->historyBuilder->receivedAt($date->subDays(5))->create();
        $m_3 = $this->historyBuilder->receivedAt($date->subDays(3))->create();
        $m_4 = $this->historyBuilder->receivedAt($date->subDays(6))->create();


        $this->getJson(route('v1.saas.gps.history',[
            'order_type' => 'desc',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                    ['id' => $m_4->id],
                ],
                'meta' => [
                    'to' => 4,
                    'total' => 4,
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsSaasAdmin();

        $this->getJson(route('v1.saas.gps.history'))
            ->assertForbidden();
    }

    /** @test */
    public function not_auth(): void
    {
        $this->getJson(route('v1.saas.gps.history'))
            ->assertUnauthorized();
    }
}


