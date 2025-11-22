<?php

namespace Tests\Feature\Saas\GPS\Devices;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class DeviceCreateTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;

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

    public function test_permitted_admin_can_create_device(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $this->deviceSubscriptionsBuilder->company($company)->create();

        $data = [
            'name' => 'test name',
            'imei' => 'test imei',
            'device_id' => 11,
            'phone' => '+38(095)544-11-11',
            'company_id' => $company->id,
        ];

        $res = $this->postJson(route('v1.saas.gps-devices.store'), $data)
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'imei' => data_get($data, 'imei'),
                    'status' => DeviceStatus::INACTIVE,
                    'status_request' => DeviceRequestStatus::NONE(),
                    'status_activate_request' => DeviceStatusActivateRequest::NONE(),
                    'phone' => phone_clear(data_get($data, 'phone')),
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->name,
                    ],
                    'inactive_at' => null
                ]
            ])
        ;

        $model = Device::find($res->json('data.id'));

        $this->assertNull($model->active_at);
        $this->assertEquals($model->flespi_device_id, $data['device_id']);

        $this->assertEquals($model->histories[0]->context, DeviceHistoryContext::CREATE);
    }

    /** @test */
    public function create_only_required_field(): void
    {
        $this->loginAsSaasSuperAdmin();

        $data = [
            'name' => 'test name',
            'imei' => 'test imei',
            'device_id' => 11,
        ];

        $res = $this->postJson(route('v1.saas.gps-devices.store'), $data)
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'imei' => data_get($data, 'imei'),
                    'status' => DeviceStatus::INACTIVE,
                    'phone' => null,
                    'company' => null,
                    'active_at' => null
                ]
            ])
        ;

        $model = Device::find($res->json('data.id'));

        $this->assertNotNull($model->inactive_at);
    }

    /** @test */
    public function cant_create(): void
    {
        $this->loginAsSaasSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->company($company)
            ->status(DeviceSubscriptionStatus::ACTIVE_TILL())
            ->create();

        $data = [
            'name' => 'test name',
            'imei' => 'test imei',
            'device_id' => 11,
            'company_id' => $company->id,
        ];

        $res = $this->postJson(route('v1.saas.gps-devices.store'), $data);

        $this->assertResponseHasValidationMessage($res, 'company_id',
            __('validation.custom.gps.device_subscription.company_cancel_subscription')
        );
    }

    /** @test */
    public function fail_cancel_subscription(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create(), true);

        $this->deviceSubscriptionsBuilder->company($company)->create();

        $data = [
            'name' => 'test name',
            'imei' => 'test imei',
            'device_id' => 11,
            'phone' => '+38(095)544-11-11',
            'company_id' => $company->id,
        ];

        $res = $this->postJson(route('v1.saas.gps-devices.store'), $data);

        $this->assertResponseHasValidationMessage($res, 'company_id',
            __('validation.custom.company.cancel_subscription')
        );
    }

    public function test_permitted_admin_cant_create_device_with_same_imei(): void
    {
        $admin = $this->createAdmin()->assignRole(
            $this->createRoleDeviceCreator()
        );

        $this->loginAsSaasAdmin($admin);

        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $this->deviceSubscriptionsBuilder->company($company)->create();

        $data = [
            'name' => 'test name',
            'imei' => 'test imei',
            'company_id' => $company->id,
            'device_id' => 11,
        ];

        $this->postJson(route('v1.saas.gps-devices.store'), $data)
            ->assertCreated();


        $data = [
            'name' => 'test name2',
            'imei' => 'test imei',
            'company_id' => $company->id,
        ];
        $this->postJson(route('v1.saas.gps-devices.store'), $data)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing(Device::TABLE_NAME, $data);
    }


    public function test_not_auth_admin_cant_create_device(): void
    {
        $this->postJson(route('v1.saas.gps-devices.store'))
            ->assertUnauthorized();
    }

    public function test_not_permitted_admin_cant_create_device(): void
    {
        $this->loginAsSaasAdmin();

        $data = [
            'name' => 'test name',
            'imei' => 'test imei',
            'device_id' => 11,
        ];

        $this->postJson(route('v1.saas.gps-devices.store'), $data)
            ->assertForbidden();
    }
}
