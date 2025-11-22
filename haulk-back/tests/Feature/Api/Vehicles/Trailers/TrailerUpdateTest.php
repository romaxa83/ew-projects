<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Enums\Format\DateTimeEnum;
use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\Files\File;
use App\Models\GPS\History;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Feature\Api\Vehicles\VehicleUpdateTest;
use Tests\Helpers\Traits\UserFactoryHelper;

class TrailerUpdateTest extends VehicleUpdateTest
{
    use UserFactoryHelper;

    protected string $routeName = 'trailers.update';

    protected string $tableName = Trailer::TABLE_NAME;

    protected array $requestData = [];

    public array $connectionsToTransact = [
        DbConnections::DEFAULT, DbConnections::GPS
    ];

    protected CompanyBuilder $companyBuilder;
    protected HistoryBuilder $historyBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected UserBuilder $userBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

        $this->passportInit();
    }

    protected function getVehicle(array $attributes = []): Vehicle
    {
        return factory(Trailer::class)->create($attributes);
    }

    protected function getRequestData(): array
    {
        if (empty($this->requestData)) {
            $this->requestData = [
                'vin' => 'DFDFDF3234234',
                'unit_number' => 'df763',
                'make' => 'Audi',
                'model' => 'A3',
                'year' => '2020',
                'license_plate' => 'SD34343',
                'temporary_plate' => 'WEF-745',
                'notes' => 'test notes',
                'owner_id' => $this->ownerFactory()->id,
                'driver_id' => $this->driverFactory()->id,
                'driver_attach_at' => now()->format(DateTimeEnum::DATE_TIME_FRONT),
                'color' => 'red',
            ];
        }

        return $this->requestData;
    }

    protected function loginAsPermittedUser(): User
    {
        return $this->loginAsCarrierSuperAdmin();
    }

    protected function loginAsNotPermittedUser(): User
    {
        return $this->loginAsCarrierDispatcher();
    }

    public function test_it_update_with_not_unique_vin(): void
    {
        $this->loginAsPermittedUser();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $trailer = factory(Trailer::class)->create();

        $vin = 'DFDFDF3234234';
        factory(Trailer::class)->create(['vin' => $vin]);

        $requestData = [
            'vin' => $vin,
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $this->driverOwnerFactory()->id,
            'driver_id' => $this->driverFactory()->id,
            'driver_attach_at' => $date->format(DateTimeEnum::DATE_TIME_FRONT),
        ];

        $this->postJson(route($this->routeName, $trailer), $requestData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $requestData['vin'] = $trailer->vin;
        $this->postJson(route($this->routeName, $trailer), $requestData)
            ->assertOk();
    }

    public function test_it_update_with_inspection_and_registration(): void
    {
        $this->loginAsPermittedUser();
        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();
        $trailer = factory(Trailer::class)->create();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'driver_id' => $driver->id,
            'driver_attach_at' => $date->format(DateTimeEnum::DATE_TIME_FRONT),
            'registration_number' => '12dfsfsdf-df',
            'registration_date' => now()->format('m/d/Y'),
            'registration_expiration_date' => now()->format('m/d/Y'),
            'registration_file' => UploadedFile::fake()->image('image1.jpg'),
            'inspection_date' => now()->format('m/d/Y'),
            'inspection_expiration_date' => now()->format('m/d/Y'),
            'inspection_file' => UploadedFile::fake()->create('doc.pdf'),
        ];

         $response = $this->postJson(route($this->routeName, $trailer->id), $requestData)
            ->assertOk();

        $this->assertDatabaseHas($this->tableName, [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'driver_id' => $driver->id,
            'driver_attach_at' => $date->format(DateTimeEnum::DATE_TIME_BACK),
            'registration_number' => '12dfsfsdf-df',
            'registration_date' => now()->format('Y-m-d'),
            'registration_expiration_date' => now()->format('Y-m-d'),
            'inspection_date' => now()->format('Y-m-d'),
            'inspection_expiration_date' => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $trailer->id,
                'collection_name' => Vehicle::REGISTRATION_DOCUMENT_NAME,
            ]
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $trailer->id,
                'collection_name' => Vehicle::INSPECTION_DOCUMENT_NAME,
            ]
        );

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'registration_number' => '12dfsfsdf-df',
            'registration_date' => now()->format('m/d/Y'),
            'registration_expiration_date' => now()->format('m/d/Y'),
            'registration_file' => UploadedFile::fake()->image('image1.jpg'),
            'inspection_date' => now()->format('m/d/Y'),
            'inspection_expiration_date' => now()->format('m/d/Y'),
            'inspection_file' => UploadedFile::fake()->create('doc.pdf'),
        ];

        $this->postJson(route($this->routeName, $trailer->id), $requestData)
            ->assertOk();

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $trailer->id,
                'collection_name' => Vehicle::REGISTRATION_DOCUMENT_NAME,
            ]
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $trailer->id,
                'collection_name' => Vehicle::INSPECTION_DOCUMENT_NAME,
            ]
        );

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'id' => $response['data']['registration_file']['id'],
            ]
        );

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'id' => $response['data']['inspection_file']['id'],
            ]
        );
    }

    public function test_delete_registration_document(): void
    {
        $this->loginAsPermittedUser();
        $trailer = factory(Trailer::class)->create();
        $trailer->addMediaWithRandomName(
            Vehicle::REGISTRATION_DOCUMENT_NAME,
            UploadedFile::fake()->create('doc.pdf')
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $trailer->id,
                'collection_name' => Vehicle::REGISTRATION_DOCUMENT_NAME,
            ]
        );

        $this->deleteJson(route('trailers.delete-registration-document', $trailer->id))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $trailer->id,
                'collection_name' => Vehicle::REGISTRATION_DOCUMENT_NAME,
            ]
        );
    }

    public function test_delete_inspection_document(): void
    {
        $this->loginAsPermittedUser();
        $trailer = factory(Trailer::class)->create();
        $trailer->addMediaWithRandomName(
            Vehicle::INSPECTION_DOCUMENT_NAME,
            UploadedFile::fake()->create('doc.pdf')
        );

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $trailer->id,
                'collection_name' => Vehicle::INSPECTION_DOCUMENT_NAME,
            ]
        );

        $this->deleteJson(route('trailers.delete-inspection-document', $trailer->id))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_type' => Trailer::class,
                'model_id' => $trailer->id,
                'collection_name' => Vehicle::INSPECTION_DOCUMENT_NAME,
            ]
        );
    }

    public function test_it_update_with_gps_device(): void
    {
        $user = $this->loginAsPermittedUser();
        $company = $user->getCompany();
        $company->save();
        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();
        $trailer = factory(Trailer::class)->create();
        $device = Device::factory(['company_id' => $company->id])->create();

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'gps_device_id' => $device->id,
        ];

        $this->assertNull($trailer->last_gps_history_id);

        $this->postJson(route($this->routeName, $trailer->id), $requestData)
            ->assertOk();

        $trailer->refresh();

        $history = History::query()->where('trailer_id', $trailer->id)->first();

//        $this->assertNull($trailer->last_gps_history_id);
        $this->assertEquals($history->device_id, $device->id);
        $this->assertEquals($history->company_id, $company->id);
        $this->assertEquals($history->event_type, History::EVENT_ENGINE_OFF);

        $this->assertEquals($device->histories[0]->context, DeviceHistoryContext::ATTACH_TO_VEHICLE);

//        $this->assertDatabaseHas($this->tableName, $requestData);
//
//        $this->postJson(route($this->routeName, $trailer->id), $requestData)
//            ->assertOk();
    }

    public function test_gps_device_validation(): void
    {
        $user = $this->loginAsPermittedUser();
        $company = $user->getCompany();
        $company->gps_enabled = true;
        $company->save();
        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();
        $device = Device::factory(['company_id' => $company->id])->create();
        $trailer = factory(Trailer::class)->create();
        $truck = factory(Truck::class)->create(['gps_device_id' => $device->id]);

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'gps_device_id' => $device->id,
        ];

        $this->postJson(route($this->routeName, $trailer->id), $requestData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $truck->gps_device_id = null;
        $truck->save();
        factory(Trailer::class)->create(['gps_device_id' => $device->id]);

        $this->postJson(route($this->routeName, $trailer->id), $requestData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_not_update_gps_device_for_company_without_enabled_gps(): void
    {
        $user = $this->loginAsPermittedUser();
        $company = $user->getCompany();
        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();
        $device = Device::factory(['company_id' => $company->id])->create();
        $trailer = factory(Trailer::class)->create();

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'gps_device_id' => $device->id,
        ];

        $this->postJson(route($this->routeName, $trailer->id), $requestData)
            ->assertOk();

        $this->assertDatabaseHas($this->tableName, [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'gps_device_id' => null,
        ]);
    }

    /** @test */
    public function update_and_create_tracking_history(): void
    {
        $user = $this->loginAsPermittedUser();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $company = $user->getCompany();
        $company->save();

        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->driver($driver)->create();

        $h_1 = $this->historyBuilder->trailer($trailer)->driver($driver)
            ->receivedAt($date->subMinutes(10))->create();
        $h_2 = $this->historyBuilder->trailer($trailer)->driver($driver)
            ->receivedAt($date->subMinutes(1))->create();
        $h_3 = $this->historyBuilder->trailer($trailer)->driver($driver)
            ->receivedAt($date->subMinutes(8))->create();
        $h_4 = $this->historyBuilder->trailer($trailer)->driver($driver)
            ->receivedAt($date->subMinutes(5))->create();

        $trailer->last_gps_history_id = $h_2->id;
        $trailer->save();

        $this->assertCount(4, $trailer->gpsHistories);

        $newDriver = $this->userBuilder->asDriver()->create();

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'driver_id' => $newDriver->id,
        ];

        $this->postJson(route($this->routeName, $trailer->id), $requestData)
        ;

        $trailer->refresh();

        $this->assertCount(5, $trailer->gpsHistories);

        $histories = History::query()
            ->where('trailer_id', $trailer->id)
            ->orderByDesc('received_at')
            ->get()
        ;

        $this->assertEquals($trailer->last_gps_history_id, $h_2->id);

        $this->assertEquals($histories[0]->driver_id, $newDriver->id);
        $this->assertEquals($histories[0]->old_driver_id, $driver->id);
        $this->assertEquals($histories[0]->received_at, $date->format(DateTimeEnum::DATE_TIME_BACK));
        $this->assertEquals($histories[0]->longitude, $histories[2]->longitude);
        $this->assertEquals($histories[0]->latitude, $histories[2]->latitude);
        $this->assertEquals($histories[0]->speed, $histories[2]->speed);
        $this->assertEquals($histories[0]->vehicle_mileage, $histories[2]->vehicle_mileage);
        $this->assertEquals($histories[0]->heading, $histories[2]->heading);
        $this->assertEquals($histories[0]->event_type, History::EVENT_CHANGE_DRIVER);
        $this->assertEquals($histories[0]->device_id, $histories[2]->device_id);
        $this->assertEquals($histories[0]->company_id, $histories[2]->company_id);
        $this->assertEquals($histories[0]->device_battery_level, $histories[2]->device_battery_level);
        $this->assertEquals($histories[0]->device_battery_charging_status, $histories[2]->device_battery_charging_status);

        $this->assertEquals($histories[1]->id, $h_2->id);
        $this->assertEquals($histories[1]->driver_id, $driver->id);

        $this->assertEquals($histories[2]->id, $h_4->id);
        $this->assertEquals($histories[2]->driver_id, $driver->id);
        $this->assertEquals($histories[3]->id, $h_3->id);
        $this->assertEquals($histories[3]->driver_id, $driver->id);
        $this->assertEquals($histories[4]->id, $h_1->id);
        $this->assertEquals($histories[4]->driver_id, $driver->id);
    }

    /** @test */
    public function update_not_create_change_driver_event(): void
    {
        $user = $this->loginAsPermittedUser();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $company = $user->getCompany();
        $company->gps_enabled = true;
        $company->save();

        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->create();

        $this->assertCount(0, $trailer->gpsHistories);

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'driver_id' => $driver->id,
        ];

        $this->postJson(route($this->routeName, $trailer->id), $requestData)
        ;

        $trailer->refresh();

        $this->assertCount(0, $trailer->gpsHistories);
    }

    /** @test */
    public function update_not_create_change_driver_event_not_history(): void
    {
        $user = $this->loginAsPermittedUser();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $company = $user->getCompany();
        $company->gps_enabled = true;
        $company->save();

        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->driver($driver)->create();

        $this->assertCount(0, $trailer->gpsHistories);

        $newDriver = $this->userBuilder->asDriver()->create();

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'driver_id' => $newDriver->id,
        ];

        $this->postJson(route($this->routeName, $trailer->id), $requestData)
        ;

        $trailer->refresh();

        $this->assertCount(0, $trailer->gpsHistories);
    }

    /** @test */
    public function update_and_create_tracking_history_and_next_records(): void
    {
        $user = $this->loginAsPermittedUser();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $company = $user->getCompany();
        $company->gps_enabled = true;
        $company->save();

        $owner = $this->driverOwnerFactory();
        $driver = $this->driverFactory();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->driver($driver)->create();

        $h_1 = $this->historyBuilder->trailer($trailer)->driver($driver)
            ->receivedAt($date->addMinutes(10))->create();
        $h_2 = $this->historyBuilder->trailer($trailer)->driver($driver)
            ->receivedAt($date->subMinutes(2))->create();
        $h_3 = $this->historyBuilder->trailer($trailer)->driver($driver)
            ->receivedAt($date->subMinutes(8))->create();
        $h_4 = $this->historyBuilder->trailer($trailer)->driver($driver)
            ->receivedAt($date->addMinutes(5))->create();

        $trailer->last_gps_history_id = $h_1->id;
        $trailer->save();

        $this->assertCount(4, $trailer->gpsHistories);

        $newDriver = $this->userBuilder->asDriver()->create();

        $requestData = [
            'vin' => 'DFFSDYU2344',
            'unit_number' => 'df763',
            'make' => 'Audi',
            'model' => 'A3',
            'year' => '2020',
            'type' => Vehicle::VEHICLE_TYPE_COUPE_2,
            'license_plate' => 'SD34343',
            'temporary_plate' => null,
            'notes' => 'test notes',
            'owner_id' => $owner->id,
            'driver_id' => $newDriver->id,
        ];

        $this->postJson(route($this->routeName, $trailer->id), $requestData)
        ;

        $trailer->refresh();

        $this->assertCount(5, $trailer->gpsHistories);

        $histories = History::query()
            ->where('trailer_id', $trailer->id)
            ->orderByDesc('received_at')
            ->get()
        ;

        $this->assertEquals($trailer->last_gps_history_id, $h_1->id);

        $this->assertEquals($histories[0]->id, $h_1->id);
        $this->assertEquals($histories[0]->driver_id, $newDriver->id);

        $this->assertEquals($histories[1]->id, $h_4->id);
        $this->assertEquals($histories[1]->driver_id, $newDriver->id);

        $this->assertEquals($histories[2]->driver_id, $newDriver->id);
        $this->assertEquals($histories[2]->old_driver_id, $driver->id);
        $this->assertEquals($histories[2]->event_type, History::EVENT_CHANGE_DRIVER);

        $this->assertEquals($histories[3]->id, $h_2->id);
        $this->assertEquals($histories[3]->driver_id, $driver->id);

        $this->assertEquals($histories[4]->id, $h_3->id);
        $this->assertEquals($histories[4]->driver_id, $driver->id);
    }
}
