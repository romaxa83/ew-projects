<?php

namespace Feature\Api\GPS\DeviceRequest;

use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Saas\GPS\DeviceSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Billing\InvoiceBuilder;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;
    protected InvoiceBuilder $invoiceBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->invoiceBuilder = resolve(InvoiceBuilder::class);
    }

    /** @test */
    public function success_create(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $data = [
            'qty' => 4
        ];

        $this->assertFalse(DeviceRequest::query()->where('company_id', $company->id)->exists());
        $this->assertNull($company->gpsDeviceSubscription);

        $this->postJson(route('gps.device.request.create'),$data)
            ->assertJson([
                'data' => true
            ])
        ;

        /** @var $model DeviceRequest */
        $model = DeviceRequest::query()->where('company_id', $company->id)->first();

        $this->assertEquals($model->user->id, $user->id);
        $this->assertEquals($model->company->id, $company->id);
        $this->assertEquals($model->qty, $data['qty']);
        $this->assertTrue($model->status->isNew());
        $this->assertNull($model->closed_at);
        $this->assertTrue($model->source->isCRM());

        $company->refresh();

        $this->assertEquals($company->gpsDeviceSubscription->status, DeviceSubscriptionStatus::DRAFT());
        $this->assertNull($company->gpsDeviceSubscription->activate_at);
        $this->assertNull($company->gpsDeviceSubscription->activate_till_at);
        $this->assertNull($company->gpsDeviceSubscription->canceled_at);
        $this->assertNull($company->gpsDeviceSubscription->next_rate);
        $this->assertEquals(
            $company->gpsDeviceSubscription->current_rate,
            config('billing.gps.price')
        );
    }

    /** @test */
    public function success_create_exist_subscription(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $this->deviceSubscriptionsBuilder->company($company)->create();

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $data = [
            'qty' => 4
        ];

        $this->assertEquals(1, DeviceSubscription::query()->where('company_id', $company->id)->count());

        $this->postJson(route('gps.device.request.create'),$data)
            ->assertJson([
                'data' => true
            ])
        ;

        $this->assertEquals(1, DeviceSubscription::query()->where('company_id', $company->id)->count());
    }

    /** @test */
    public function fail_create_has_unpaid_invoice(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());
        $this->invoiceBuilder->company($company)->unpaid()->create();

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        $data = [
            'qty' => 4
        ];

        $this->assertTrue($company->hasUnpaidInvoices());

        $res = $this->postJson(route('gps.device.request.create'),$data)
        ;

        $this->assertResponseErrorMessage($res, __('exceptions.company.billing.has_unpaid_invoice', [
            'company_name' => $company->name
        ]), 401);
    }
}


