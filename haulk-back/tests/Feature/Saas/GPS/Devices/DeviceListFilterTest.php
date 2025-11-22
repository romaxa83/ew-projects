<?php

namespace Tests\Feature\Saas\GPS\Devices;

use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\ValueObjects\Phone;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\TestCase;

class DeviceListFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceBuilder $deviceBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
    }

    public function test_search_devices(): void
    {
        $this->loginAsSaasSuperAdmin();

        Device::factory()->create(['imei' => 'Imai1']);
        Device::factory()->create(['imei' => 'Imai2']);
        Device::factory()->create(['imei' => 'Imai3', 'deleted_at' => CarbonImmutable::now()]);
        Device::factory()->create(['imei' => 'test1']);
        Device::factory()->create(['imei' => 'test2']);

        $args = [
            'query' => 'Imai',
        ];

        $devices = $this->getJson(route('v1.saas.gps-devices.index', $args))
            ->assertOk();

        self::assertCount(3, $devices['data']);
    }

    public function test_filter_by_company_devices(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        Device::factory()->create(['company_id' => $company1->id]);
        Device::factory()->create(['company_id' => $company1->id]);
        Device::factory()->create(['company_id' => $company2->id]);

        $args = [
            'company_id' => $company2->id,
        ];

        $devices = $this->getJson(route('v1.saas.gps-devices.index', $args))
            ->assertOk();

        self::assertCount(1, $devices['data']);
    }

    /** @test */
    public function filter_by_status(): void
    {
        $this->loginAsSaasSuperAdmin();

        Device::factory()->create(['status' => DeviceStatus::INACTIVE()]);
        Device::factory()->create(['status' => DeviceStatus::ACTIVE()]);
        Device::factory()->create(['status' => DeviceStatus::ACTIVE()]);
        Device::factory()->create(['status' => DeviceStatus::DELETED()]);

        $this->getJson(route('v1.saas.gps-devices.index', [
            'status' => DeviceStatus::INACTIVE
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function filter_by_request_status(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->deviceBuilder->statusRequest(DeviceRequestStatus::PENDING())->create();
        $this->deviceBuilder->statusRequest(DeviceRequestStatus::PENDING())->create();
        $this->deviceBuilder->statusRequest(DeviceRequestStatus::PENDING())->create();
        $this->deviceBuilder->statusRequest(DeviceRequestStatus::CLOSED())->create();
        $this->deviceBuilder->create();

        $this->getJson(route('v1.saas.gps-devices.index', [
            'status_request' => DeviceRequestStatus::PENDING
        ]))
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'total' => 3
                ]
            ])
        ;
    }

    /** @test */
    public function filter_by_id(): void
    {
        $this->loginAsSaasSuperAdmin();

        $model = $this->deviceBuilder->statusRequest(DeviceRequestStatus::PENDING())->create();
        $this->deviceBuilder->statusRequest(DeviceRequestStatus::PENDING())->create();
        $this->deviceBuilder->statusRequest(DeviceRequestStatus::PENDING())->create();
        $this->deviceBuilder->statusRequest(DeviceRequestStatus::CLOSED())->create();
        $this->deviceBuilder->create();

        $this->getJson(route('v1.saas.gps-devices.index', [
            'id' => $model->id
        ]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $model->id]
                ],
                'meta' => [
                    'total' => 1
                ]
            ])
        ;
    }

    /** @test */
    public function filter_by_ids(): void
    {
        $this->loginAsSaasSuperAdmin();

        $model = $this->deviceBuilder->statusRequest(DeviceRequestStatus::PENDING())->create();
        $model_2 = $this->deviceBuilder->statusRequest(DeviceRequestStatus::PENDING())->create();
        $this->deviceBuilder->statusRequest(DeviceRequestStatus::PENDING())->create();
        $this->deviceBuilder->statusRequest(DeviceRequestStatus::CLOSED())->create();
        $this->deviceBuilder->create();

        $this->getJson(route('v1.saas.gps-devices.index', [
            'id' => [$model->id, $model_2->id]
        ]))
            ->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => $model->id]
                ],
                'meta' => [
                    'total' => 2
                ]
            ])
        ;
    }

    /** @test */
    public function search_by_phone(): void
    {
        $this->loginAsSaasSuperAdmin();

        Device::factory()->create(['phone' => new Phone('19785555555')]);
        Device::factory()->create(['phone' => new Phone('19784444444'), 'deleted_at' => CarbonImmutable::now()]);
        Device::factory()->create(['phone' => new Phone('29785555555')]);

        $this->getJson(route('v1.saas.gps-devices.index', [
            'query' => '1978'
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function search_by_phone_and_imei(): void
    {
        $this->loginAsSaasSuperAdmin();

        Device::factory()->create([
            'phone' => new Phone('19785555555'),
            'imei' => '444419785555555'
        ]);
        Device::factory()->create(['phone' => new Phone('29784444444'), 'imei' => '5555555']);
        Device::factory()->create(['phone' => new Phone('29785555555'), 'imei' => '44441']);

        $this->getJson(route('v1.saas.gps-devices.index', [
            'query' => '1978'
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
        ;
    }
}
