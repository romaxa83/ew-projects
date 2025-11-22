<?php

namespace Tests\Feature\Api\GPS;

use App\Models\GPS\Alert;
use App\Models\Saas\Company\Company;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\AlertBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class AlertIndexTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected AlertBuilder $alertBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->alertBuilder = resolve(AlertBuilder::class);
    }

    public function test_index()
    {
        $user = $this->loginAsCarrierSuperAdmin();

        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'received_at' => now()]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'received_at' => now()->subDay()]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'received_at' => now()->subDays(2)]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'received_at' => now()->subDays(6)]);
        Alert::factory()->create(['company_id' => (Company::factory()->create())->id, 'received_at' => now()->subDays(6)]);

        $response = $this->getJson(route('gps.alerts-index'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'received_at',
                        'vehicle_unit_number',
                        'driver_name',
                        'alert_type',
                        'details',
                        'latitude',
                        'longitude',
                        'last_driving_at',
                    ],
                ],
            ]);

        $this->assertCount(3, $response['data']);
    }

    public function test_filter_by_truck_id()
    {
        $user = $this->loginAsCarrierSuperAdmin();

        $truck1 = factory(Truck::class)->create();
        $truck2 = factory(Truck::class)->create();

        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'truck_id' => $truck1->id]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'truck_id' => $truck1->id]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'truck_id' => $truck1->id]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'truck_id' => $truck2->id]);

        $response = $this->getJson(route('gps.alerts-index', ['truck_id' => $truck1->id]))
            ->assertOk();

        $this->assertCount(3, $response['data']);
    }

    public function test_filter_by_trailer_id()
    {
        $user = $this->loginAsCarrierSuperAdmin();

        $trailer1 = factory(Trailer::class)->create();
        $trailer2 = factory(Trailer::class)->create();

        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'trailer_id' => $trailer1->id]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'trailer_id' => $trailer1->id]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'trailer_id' => $trailer1->id]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'trailer_id' => $trailer2->id]);

        $response = $this->getJson(route('gps.alerts-index', ['trailer_id' => $trailer1->id]))
            ->assertOk();

        $this->assertCount(3, $response['data']);
    }

    public function test_filter_by_vehicle_unit_number()
    {
        $date = CarbonImmutable::now();

        $user = $this->loginAsCarrierSuperAdmin();

        $truck_1 = $this->truckBuilder->unitNumber('490000')->create();
        $truck_2 = $this->truckBuilder->unitNumber('490111')->create();
        $truck_3 = $this->truckBuilder->unitNumber('590000')->create();

        $trailer_1 = $this->trailerBuilder->unitNumber('490222')->create();
        $trailer_2 = $this->trailerBuilder->unitNumber('590222')->create();

        $m_1 = $this->alertBuilder->truck($truck_1)->companyId($user->getCompanyId())->receivedAt($date->subMinutes(15))->create();
        $m_2 = $this->alertBuilder->truck($truck_1)->companyId($user->getCompanyId())->receivedAt($date->subMinutes(14))->create();
        $m_3 = $this->alertBuilder->truck($truck_2)->companyId($user->getCompanyId())->receivedAt($date->subMinutes(13))->create();
        $m_4 = $this->alertBuilder->truck($truck_3)->companyId($user->getCompanyId())->receivedAt($date->subMinutes(12))->create();
        $m_5 = $this->alertBuilder->truck($truck_3)->companyId($user->getCompanyId())->receivedAt($date->subMinutes(11))->create();
        $m_6 = $this->alertBuilder->trailer($trailer_1)->companyId($user->getCompanyId())->receivedAt($date->subMinutes(10))->create();
        $m_7 = $this->alertBuilder->trailer($trailer_1)->companyId($user->getCompanyId())->receivedAt($date->subMinutes(9))->create();
        $m_8 = $this->alertBuilder->trailer($trailer_2)->companyId($user->getCompanyId())->receivedAt($date->subMinutes(8))->create();

        $this->getJson(route('gps.alerts-index', [
            'vehicle_unit_number' => '490'
        ]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $m_7->id],
                    ['id' => $m_6->id],
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                ]
            ])
            ->assertJsonCount(5, 'data')
        ;
    }

    public function test_filter_by_driver_id()
    {
        $user = $this->loginAsCarrierSuperAdmin();

        $driver1 = $this->driverFactory();
        $driver2 = $this->driverFactory();

        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'driver_id' => $driver1->id]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'driver_id' => $driver1->id]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'driver_id' => $driver2->id]);

        $response = $this->getJson(route('gps.alerts-index', ['driver_id' => $driver1->id]))
            ->assertOk();

        $this->assertCount(2, $response['data']);
    }

    public function test_filter_by_alert_type()
    {
        $user = $this->loginAsCarrierSuperAdmin();

        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'alert_type' => Alert::ALERT_TYPE_SPEEDING]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'alert_type' => Alert::ALERT_TYPE_SPEEDING]);
        Alert::factory()->create(['company_id' => $user->getCompanyId(), 'alert_type' => Alert::ALERT_TYPE_DEVICE_CONNECTION]);

        $response = $this->getJson(route('gps.alerts-index', ['alert_type' => Alert::ALERT_TYPE_DEVICE_CONNECTION]))
            ->assertOk();

        $this->assertCount(1, $response['data']);
    }

    public function test_sorting()
    {
        $user = $this->loginAsCarrierSuperAdmin();

        $alert1 = Alert::factory()->create(['company_id' => $user->getCompanyId(), 'received_at' => now()]);
        $alert2 = Alert::factory()->create(['company_id' => $user->getCompanyId(), 'received_at' => now()->subDay()]);
        $alert3 = Alert::factory()->create(['company_id' => $user->getCompanyId(), 'received_at' => now()->subDays(2)]);

        $response = $this->getJson(route('gps.alerts-index'))
            ->assertOk();

        $this->assertEquals([$alert1->id, $alert2->id,$alert3->id], array_column($response['data'], 'id'));

        $response = $this->getJson(route('gps.alerts-index', ['order_type' => 'asc']))
            ->assertOk();

        $this->assertEquals([$alert3->id, $alert2->id,$alert1->id], array_column($response['data'], 'id'));
    }
}
