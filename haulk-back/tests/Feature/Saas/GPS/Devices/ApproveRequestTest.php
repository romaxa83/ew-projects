<?php

namespace Tests\Feature\Saas\GPS\Devices;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Alerts\Alert;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class ApproveRequestTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected CompanyBuilder $companyBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected UserBuilder $userBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_approve_activate(): void
    {
        $this->loginAsSaasSuperAdmin();

        $currentRate = 2;

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $userSendRequest = $this->userBuilder->asDispatcher()->company($company)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::INACTIVE())
            ->statusRequest(DeviceRequestStatus::PENDING())
            ->statusActivateRequest(DeviceStatusActivateRequest::ACTIVATE())
            ->sendRequestUser($userSendRequest)
            ->create();

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->company($company)
            ->currentRate($currentRate)
            ->create();

        $this->assertNull($subscription->activate_at);
        $this->assertTrue($subscription->status->isDraft());

        $this->assertNull($model->active_at);
        $this->assertNull($model->request_closed_at);

        $this->assertFalse(Alert::query()->where('carrier_id', $company->id)->exists());

        $this->assertEmpty($model->histories);

        $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => DeviceStatus::ACTIVE(),
                    'status_request' => DeviceRequestStatus::CLOSED(),
                    'status_activate_request' => DeviceStatusActivateRequest::NONE(),
                    'active_at' => $date->timestamp,
                ]
            ]);

        $model->refresh();
        $company->refresh();

        $this->assertEquals($model->request_closed_at->timestamp, $date->timestamp);

        $subscription = $company->gpsDeviceSubscription;
        $this->assertEquals($subscription->status, DeviceSubscriptionStatus::ACTIVE);
        $this->assertEquals($subscription->activate_at->timestamp, $date->timestamp);
        $this->assertEquals($subscription->current_rate, $currentRate);

        $alert = Alert::query()->where('carrier_id', $company->id)->first();
        $this->assertEquals($alert->type, Alert::DEVICE_TOGGLE_ACTIVITY);
        $this->assertEquals($alert->message, 'notification.device.for_crm.activate');

        $this->assertEquals($model->histories[0]->context, DeviceHistoryContext::ACTIVATE);
        $this->assertEquals(
            $model->histories[0]->changed_data['new']['status'],
            DeviceStatus::ACTIVE
        );
        $this->assertEquals(
            $model->histories[0]->changed_data['old']['status'],
            DeviceStatus::INACTIVE
        );
    }

    /** @test */
    public function success_approve_activate_change_rate(): void
    {
        $this->loginAsSaasSuperAdmin();

        $currentRate = 2;
        $nextRate = 3;

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $userSendRequest = $this->userBuilder->asDispatcher()->company($company)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::INACTIVE())
            ->statusRequest(DeviceRequestStatus::PENDING())
            ->statusActivateRequest(DeviceStatusActivateRequest::ACTIVATE())
            ->sendRequestUser($userSendRequest)
            ->create();

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->company($company)
            ->currentRate($currentRate)
            ->nextRate($nextRate)
            ->create();

        $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => DeviceStatus::ACTIVE(),
                    'status_request' => DeviceRequestStatus::CLOSED(),
                    'status_activate_request' => DeviceStatusActivateRequest::NONE(),
                    'active_at' => $date->timestamp,
                ]
            ]);

        $model->refresh();
        $company->refresh();

        $subscription = $company->gpsDeviceSubscription;

        $this->assertEquals($subscription->current_rate, $nextRate);
        $this->assertNull($subscription->next_rate);
    }

    /** @test */
    public function success_approve_activate_if_subscription_cancel(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $userSendRequest = $this->userBuilder->asDispatcher()->company($company)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::INACTIVE())
            ->statusRequest(DeviceRequestStatus::PENDING())
            ->statusActivateRequest(DeviceStatusActivateRequest::ACTIVATE())
            ->sendRequestUser($userSendRequest)
            ->create();

        $anotherDevice_1 = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->activeTillAt($date->addDay())
            ->statusRequest(DeviceRequestStatus::CANCEL_SUBSCRIPTION())
            ->company($company)->create();
        $anotherDevice_2 = $this->deviceBuilder
            ->status(DeviceStatus::INACTIVE())
            ->activeTillAt($date->addDay())
            ->statusRequest(DeviceRequestStatus::CANCEL_SUBSCRIPTION())
            ->company($company)->create();

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::CANCELED())
            ->accessTillAt($date->addDay())
            ->canceledAt($date->subDay())
            ->sendWarningNotify()
            ->company($company)
            ->create();

        $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => DeviceStatus::ACTIVE(),
                    'status_request' => DeviceRequestStatus::CLOSED(),
                    'status_activate_request' => DeviceStatusActivateRequest::NONE(),
                    'active_at' => $date->timestamp,
                ]
            ]);

        $subscription->refresh();

        $this->assertTrue($subscription->status->isActive());
        $this->assertNull($subscription->canceled_at);
        $this->assertNull($subscription->access_till_at);
        $this->assertFalse($subscription->send_warning_notify);
        $this->assertEquals($subscription->activate_at->timestamp, $date->timestamp);

        $anotherDevice_1->refresh();
        $anotherDevice_2->refresh();

        $this->assertTrue($anotherDevice_1->status_request->isNone());
        $this->assertNull($anotherDevice_1->active_till_at);

        $this->assertTrue($anotherDevice_2->status_request->isNone());
        $this->assertNotNull($anotherDevice_2->active_till_at);
    }

    /** @test */
    public function success_approve_activate_without_status(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $company = $this->companyBuilder->create();

        $userSendRequest = $this->userBuilder->asDispatcher()->company($company)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::INACTIVE())
            ->statusRequest(DeviceRequestStatus::NONE())
            ->statusActivateRequest(DeviceStatusActivateRequest::ACTIVATE())
            ->sendRequestUser($userSendRequest)
            ->create();

        $subscription = $this->deviceSubscriptionsBuilder->company($company)->create();

        $this->assertNull($model->active_at);
        $this->assertNull($model->request_closed_at);

        $this->assertNotNull($company->gpsDeviceSubscription);

        $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => DeviceStatus::ACTIVE(),
                    'status_request' => DeviceRequestStatus::NONE(),
                    'status_activate_request' => DeviceStatusActivateRequest::NONE(),
                    'active_at' => $date->timestamp,
                ]
            ]);

        $model->refresh();
        $company->refresh();

        $this->assertNull($model->request_closed_at);

        $this->assertNotNull($company->gpsDeviceSubscription);
    }

    /** @test */
    public function success_approve_deactivate_but_not_inactive_device(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $userSendRequest = $this->userBuilder->asDispatcher()->company($company)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())
            ->statusRequest(DeviceRequestStatus::PENDING())
            ->statusActivateRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->sendRequestUser($userSendRequest)
            ->create();

        $subscription = $this->deviceSubscriptionsBuilder->company($company)->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder->device($model)->create();

        $this->assertNull($model->inactive_at);
        $this->assertNull($model->request_closed_at);

        $this->assertFalse(Alert::query()->where('carrier_id', $company->id)->exists());

        $this->assertEmpty($model->histories);

        $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => DeviceStatus::ACTIVE(),
                    'status_request' => DeviceRequestStatus::CLOSED(),
                    'status_activate_request' => DeviceStatusActivateRequest::NONE,
                    'active_till_at' => $company->subscription->billing_end->timestamp,
                    'inactive_at' => null,
                ]
            ]);

        $truck->refresh();

        $this->assertEquals($truck->gps_device_id, $model->id);

        $model->refresh();

        $this->assertEquals($model->request_closed_at->timestamp, $date->timestamp);

        $alert = Alert::query()->where('carrier_id', $company->id)->first();
        $this->assertEquals($alert->type, Alert::DEVICE_TOGGLE_ACTIVITY);
        $this->assertEquals($alert->message, 'notification.device.for_crm.deactivate');

        $this->assertEquals($model->histories[0]->context, DeviceHistoryContext::ACTIVATE_TILL);
        $this->assertNotNull($model->histories[0]->changed_data['new']['active_till_at']);
        $this->assertNull($model->histories[0]->changed_data['old']['active_till_at']);
    }

    /** @test */
    public function success_approve_deactivate(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        $userSendRequest = $this->userBuilder->asDispatcher()->company($company)->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())
            ->statusRequest(DeviceRequestStatus::PENDING())
            ->statusActivateRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->sendRequestUser($userSendRequest)
            ->create();

        $this->deviceSubscriptionsBuilder->company($company)
            ->status(DeviceSubscriptionStatus::ACTIVE())->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder->device($model)->create();

        $this->assertNull($model->inactive_at);
        $this->assertNull($model->request_closed_at);
        $this->assertNull($model->active_till_at);

        $this->assertFalse(Alert::query()->where('carrier_id', $company->id)->exists());

        $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => DeviceStatus::ACTIVE(),
                    'status_request' => DeviceRequestStatus::CLOSED(),
                    'status_activate_request' => DeviceStatusActivateRequest::NONE,
                    'inactive_at' => null,
                    'active_till_at' => $company->subscription->billing_end->timestamp,
                ]
            ]);

        $truck->refresh();

        $this->assertEquals($truck->gps_device_id, $model->id);

        $model->refresh();

        $this->assertEquals($model->request_closed_at->timestamp, $date->timestamp);

        $alert = Alert::query()->where('carrier_id', $company->id)->first();
        $this->assertEquals($alert->type, Alert::DEVICE_TOGGLE_ACTIVITY);
        $this->assertEquals($alert->message, 'notification.device.for_crm.deactivate');
    }


    /** @test */
    public function fail_approve_device_is_closed(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $company = $this->companyBuilder->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())
            ->statusRequest(DeviceRequestStatus::CLOSED())
            ->statusActivateRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->create();

        $this->assertNull($model->inactive_at);

        $res = $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]));

        $this->assertResponseErrorMessage($res, __('exceptions.gps_device.device_must_be_pending'), 400);
    }

    /** @test */
    public function fail_subscription_canceled(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $company = $this->companyBuilder->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())
            ->statusRequest(DeviceRequestStatus::PENDING())
            ->statusActivateRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->create();

        $this->deviceSubscriptionsBuilder
            ->company($company)
            ->status(DeviceSubscriptionStatus::ACTIVE_TILL())
            ->create();

        $this->assertNull($model->inactive_at);

        $res = $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]));

        $this->assertResponseErrorMessage($res, __('exceptions.gps_device.subscription.subscription_disabled'), 400);
    }

    /** @test */
    public function fail_approve_device_not_phone(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $company = $this->companyBuilder->create();

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)
            ->phone(null)
            ->status(DeviceStatus::INACTIVE())
            ->statusRequest(DeviceRequestStatus::PENDING())
            ->statusActivateRequest(DeviceStatusActivateRequest::ACTIVATE())
            ->create();

        $res = $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]));

        $this->assertResponseErrorMessage($res, __("exceptions.gps_device.no_activate_not_company_or_phone"), 400);
    }


    /** @test */
    public function not_perm(): void
    {
        $this->loginAsSaasAdmin();

        /** @var $model Device */
        $model = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->statusRequest(DeviceRequestStatus::PENDING())
            ->statusActivateRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->create();

        $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]))
            ->assertForbidden();
    }

    /** @test */
    public function not_auth(): void
    {
        $model = $this->deviceBuilder->create();

        $this->putJson(route('v1.saas.gps-devices.approve-request', [$model]))
            ->assertUnauthorized();
    }
}


