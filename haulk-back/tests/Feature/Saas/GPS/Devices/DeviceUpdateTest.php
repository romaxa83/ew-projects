<?php

namespace Tests\Feature\Saas\GPS\Devices;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\GPS\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class DeviceUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company_1 = $this->companyBuilder->trial($this->companyBuilder->create());
        $company_2 = $this->companyBuilder->trial($this->companyBuilder->create());

        $this->deviceSubscriptionsBuilder->company($company_1)->create();
        $this->deviceSubscriptionsBuilder->company($company_2)->create();

        $this->deviceBuilder->status(DeviceStatus::INACTIVE())
            ->company($company_2)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company_1)->status(DeviceStatus::INACTIVE())->create();

        $data = [
            'imei' => $model->imei,
            'name' => 'test name',
            'company_id' => $company_2->id,
            'phone' => $model->phone->getValue(),
        ];

        $this->assertNotEquals($model->name, $data['name']);
        $this->assertNotEquals($model->company_id, $data['company_id']);

        $this->putJson(route('v1.saas.gps-devices.update', [$model]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'imei' => $model->imei,
                    'name' => $data['name'],
                    'status' => DeviceStatus::INACTIVE(),
                    'company' => [
                        'id' => $data['company_id']
                    ]
                ]
            ]);

        $company_2->refresh();

        $model->refresh();

        $this->assertEquals($model->histories[0]->context, DeviceHistoryContext::EDIT);
    }

    /** @test */
    public function success_update_company_in_active_device(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company_2 = $this->companyBuilder->trial($this->companyBuilder->create());

        $this->deviceSubscriptionsBuilder->company($company_2)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->status(DeviceStatus::ACTIVE())->create();

        $data = [
            'imei' => 'new imei',
            'name' => 'test name',
            'company_id' => $company_2->id,
            'phone' => $model->phone->getValue(),
        ];

        $this->assertNotEquals($model->name, $data['name']);
        $this->assertNotEquals($model->imei, $data['imei']);
        $this->assertNull($model->company_id);

        $this->putJson(route('v1.saas.gps-devices.update', ['id' => $model]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $data['name'],
                    'imei' => $data['imei'],
                    'status' => DeviceStatus::ACTIVE(),
                    'company' => [
                        'id' => $data['company_id']
                    ],
                    'inactive_at' => null
                ]
            ]);
    }

    /** @test */
    public function fail_update_company_gps_enabled_at_have_active_device(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company_1 = $this->companyBuilder->trial($this->companyBuilder->create());

        $this->deviceSubscriptionsBuilder->company($company_1)->create();

        $this->deviceBuilder->status(DeviceStatus::ACTIVE())->company($company_1)->create();
        $this->deviceBuilder->status(DeviceStatus::INACTIVE())->company($company_1)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company_1)
            ->withoutPhone()
            ->status(DeviceStatus::INACTIVE())
            ->create();

        $data = [
            'imei' => $model->imei,
            'name' => 'test name',
            'phone' => null,
            'company_id' => $model->company_id,
        ];

        $this->assertFalse($model->company->isGPSEnabled());

        $this->putJson(route('v1.saas.gps-devices.update', ['id' => $model]), $data)
        ;

        $model->refresh();

        $this->assertFalse($model->company->isGPSEnabled());
    }

    /** @test */
    public function fail_update_company_cancel_subscription(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company_1 = $this->companyBuilder->trial($this->companyBuilder->create(), true);

        $this->deviceSubscriptionsBuilder->company($company_1)->create();

        $this->deviceBuilder->status(DeviceStatus::INACTIVE())->company($company_1)->create();
        $this->deviceBuilder->status(DeviceStatus::INACTIVE())->company($company_1)->create();

        $model = $this->deviceBuilder->status(DeviceStatus::ACTIVE())->create();

        $data = [
            'imei' => $model->imei,
            'name' => 'test name',
            'phone' => null,
            'company_id' => $company_1->id,
        ];

        $res = $this->putJson(route('v1.saas.gps-devices.update', ['id' => $model]), $data)
        ;

        $this->assertResponseHasValidationMessage($res, 'company_id',
            __('validation.custom.company.cancel_subscription'),
            422
        );


    }
    /** @test */
    public function fail_exist_update_imei(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company_2 = $this->companyBuilder->create();

        $model_1 = $this->deviceBuilder->status(DeviceStatus::INACTIVE())->create();
        $model = $this->deviceBuilder->status(DeviceStatus::INACTIVE())->create();

        $data = [
            'name' => 'test name',
            'imei' => $model_1->imei,
            'company_id' => $company_2->id,
            'phone' => $model->phone->getValue(),
        ];

        $this->assertNotEquals($model->name, $data['name']);
        $this->assertNotEquals($model->imei, $data['imei']);
        $this->assertNull($model->company_id);

        $res = $this->putJson(route('v1.saas.gps-devices.update', ['id' => $model]), $data)
        ;

        $this->assertResponseHasValidationMessage($res, 'imei',
            __('validation.unique', ['attribute' => 'imei']),
            422
        );
    }

    /** @test */
    public function fail_update_company_if_device_active(): void
    {
        $this->loginAsSaasSuperAdmin();

        $company_1 = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        $model = $this->deviceBuilder->company($company_1)->create();

        $data = [
            'name' => 'test name',
            'imei' => $model->imei,
            'company_id' => $company_2->id,
            'phone' => $model->phone->getValue(),
        ];

        $this->assertNotEquals($model->company_id , $data['company_id']);

        $res = $this->putJson(route('v1.saas.gps-devices.update', ['id' => $model]), $data)
        ;

        $this->assertResponseHasValidationMessage($res, 'company_id',
            __('validation.custom.gps.device.cant_change_company_active_device'),
            422
        );
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsSaasAdmin();

        $model = $this->deviceBuilder->create();

        $data = [
            'name' => 'test name',
            'imei' => 'test imei',
            'phone' => $model->phone->getValue(),
        ];

        $this->putJson(route('v1.saas.gps-devices.update', ['id' => $model]), $data)
            ->assertForbidden();
    }

    /** @test */
    public function not_auth(): void
    {
        $model = $this->deviceBuilder->create();

        $this->putJson(route('v1.saas.gps-devices.update', ['id' => $model->id]), [])
            ->assertUnauthorized();
    }
}

