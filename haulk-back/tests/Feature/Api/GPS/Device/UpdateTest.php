<?php

namespace Tests\Feature\Api\GPS\Device;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Billing\InvoiceBuilder;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;
    protected InvoiceBuilder $invoiceBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->invoiceBuilder = resolve(InvoiceBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)->create();

        $data = [
            'company_device_name' => 'test_update'
        ];

        $this->assertNull($model->company_device_name);
        $this->assertFalse($company->hasUnpaidInvoices());

        $this->putJson(route('gps.device-update-api', [$model]),$data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'company_device_name' => data_get($data, 'company_device_name'),
                ]
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->histories[0]->context, DeviceHistoryContext::EDIT_COMPANY());
    }

    /** @test */
    public function fail_update_has_unpaid_invoice(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);
        $this->invoiceBuilder->company($company)->unpaid()->create();

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)->create();

        $data = [
            'company_device_name' => 'test_update'
        ];

        $this->assertTrue($company->hasUnpaidInvoices());

        $res = $this->putJson(route('gps.device-update-api', [$model]),$data)
        ;

        $this->assertResponseErrorMessage($res, __('exceptions.company.billing.has_unpaid_invoice', [
            'company_name' => $company->name
        ]), 401);
    }
}

