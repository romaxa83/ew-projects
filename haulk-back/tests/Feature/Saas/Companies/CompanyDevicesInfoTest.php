<?php

namespace Tests\Feature\Saas\Companies;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\Company\Company;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class CompanyDevicesInfoTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function get_info(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $model Company */
        $model = $this->companyBuilder
            ->create();

        $this->deviceBuilder->status(DeviceStatus::INACTIVE())->company($model)->create();
        $this->deviceBuilder->status(DeviceStatus::INACTIVE())->company($model)->create();
        $this->deviceBuilder->status(DeviceStatus::INACTIVE())->company($model)->create();
        $this->deviceBuilder->status(DeviceStatus::ACTIVE())->activeAt($date->subHours(4))->company($model)->create();
        $this->deviceBuilder->status(DeviceStatus::ACTIVE())->activeAt($date->subHours(3))->company($model)->create();
        $this->deviceBuilder->status(DeviceStatus::ACTIVE())->activeAt($date->subHours(10))->company($model)->create();
        $this->deviceBuilder->status(DeviceStatus::ACTIVE())->company($model)->create();

        $this->getJson(route('v1.saas.companies.devices-info', [$model->id]))
            ->assertJson([
                'data' => [
                    'total_device' => 7,
                    'total_active_device' => 4,
                    'total_inactive_device' => 3,
                ]
            ]);
    }

    /** @test */
    public function get_info_enable_gps(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $model Company */
        $model = $this->companyBuilder
            ->create();

        $this->deviceBuilder->status(DeviceStatus::INACTIVE())->company($model)->create();
        $this->deviceBuilder->status(DeviceStatus::INACTIVE())->company($model)->create();
        $this->deviceBuilder->status(DeviceStatus::INACTIVE())->company($model)->create();


        $this->getJson(route('v1.saas.companies.devices-info', [$model->id]))
            ->assertJson([
                'data' => [
                    'total_device' => 3,
                    'total_active_device' => 0,
                    'total_inactive_device' => 3,
                ]
            ]);
    }

    /** @test */
    public function get_info_empty(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $model Company */
        $model = $this->companyBuilder
            ->create();

        $this->getJson(route('v1.saas.companies.devices-info', [$model->id]))
            ->assertJson([
                'data' => [
                    'total_device' => 0,
                    'total_active_device' => 0,
                    'total_inactive_device' => 0,
                ]
            ]);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsSaasAdmin();

        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $this->getJson(route('v1.saas.companies.devices-info', [$model->id]))
            ->assertForbidden();
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $this->getJson(route('v1.saas.companies.devices-info', [$model->id]))
            ->assertUnauthorized();
    }
}


