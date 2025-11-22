<?php

namespace Tests\Feature\Saas\GPS\Devices;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\GPS\Device;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\TestCase;

class DeviceListTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceBuilder $deviceBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
    }

    public function test_not_permitted_admin_can_not_view_deviced(): void
    {
        $this->loginAsSaasAdmin();

        $this->getJson(route('v1.saas.gps-devices.index'))
            ->assertForbidden();
    }

    public function test_permitted_admin_can_view_devices(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.gps-devices.index'))
            ->assertOk();
    }

    /** @test */
    public function device_deleted_date_force_deleted(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $device = $this->deviceBuilder
            ->status(DeviceStatus::DELETED(), $date->subDays(10))
            ->create();

        $this->getJson(route('v1.saas.gps-devices.index'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $device->id,
                        'force_deleted_at' => $date->addDays(Device::DAYS_TO_FORCE_DELETE - 10)->timestamp
                    ]
                ]
            ]);
    }
}
