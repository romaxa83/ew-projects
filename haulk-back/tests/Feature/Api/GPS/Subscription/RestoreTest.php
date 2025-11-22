<?php

namespace Tests\Feature\Api\GPS\Subscription;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class RestoreTest extends TestCase
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

        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_restore(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $model Company */
        $model = $this->companyBuilder->trial($this->companyBuilder->create());

        $subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE_TILL())
            ->activateTillAt($date->addDays(3))
            ->company($model)
            ->create();

        /** @var $d_1 Device */
        $d_1 = $this->deviceBuilder->company($model)
            ->status(DeviceStatus::ACTIVE())
            ->activeTillAt($date->addDays(3))
            ->statusRequest(DeviceRequestStatus::CANCEL_SUBSCRIPTION())->create();
        $d_2 = $this->deviceBuilder->company($model)
            ->status(DeviceStatus::ACTIVE())
            ->activeTillAt($date->addDays(3))
            ->statusRequest(DeviceRequestStatus::CANCEL_SUBSCRIPTION())->create();
        $d_3 = $this->deviceBuilder->company($model)
            ->status(DeviceStatus::ACTIVE())
            ->activeTillAt($date->addDays(3))
            ->statusRequest(DeviceRequestStatus::CANCEL_SUBSCRIPTION())->create();
        $d_4 = $this->deviceBuilder->company($model)
            ->status(DeviceStatus::INACTIVE())
            ->statusRequest(DeviceRequestStatus::CANCEL_SUBSCRIPTION())->create();

        $this->putJson(route('gps.subscription.restore', [$subscription->id]))
            ->assertJson([
                'data' => [
                    'id' => $subscription->id,
                    'status' => DeviceSubscriptionStatus::ACTIVE(),
                    'active_till_at' => null,
                ]
            ]);

        $d_1->refresh();
        $d_2->refresh();
        $d_3->refresh();
        $d_4->refresh();

        $this->assertTrue($d_1->status_request->isNone());
        $this->assertNull($d_1->active_till_at);
        $this->assertEquals($d_1->histories[0]->context, DeviceHistoryContext::SUBSCRIPTION_RESTORE());

        $this->assertTrue($d_2->status_request->isNone());
        $this->assertNull($d_2->active_till_at);
        $this->assertEquals($d_1->histories[0]->context, DeviceHistoryContext::SUBSCRIPTION_RESTORE());

        $this->assertTrue($d_3->status_request->isNone());
        $this->assertNull($d_3->active_till_at);
        $this->assertEquals($d_1->histories[0]->context, DeviceHistoryContext::SUBSCRIPTION_RESTORE());

        $this->assertTrue($d_4->status_request->isNone());
        $this->assertNull($d_4->active_till_at);
        $this->assertEquals($d_1->histories[0]->context, DeviceHistoryContext::SUBSCRIPTION_RESTORE());
    }

    /** @test */
    public function fail_not_has_subscription(): void
    {
        $this->loginAsCarrierSuperAdmin();

        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $subscription = $this->deviceSubscriptionsBuilder->company($model)->create();

        $res = $this->putJson(route('gps.subscription.restore', ['id' => 9999]));

        $this->assertResponseErrorMessage(
            $res,
            __('exceptions.gps_device.subscription.not_active_subscription'),
            400
        );
    }

    /** @test */
    public function fail_not_restore(): void
    {
        $this->loginAsCarrierSuperAdmin();

        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::CANCELED())
            ->company($model)
            ->create();

        $res = $this->putJson(route('gps.subscription.restore', ['id' => $subscription->id]));

        $this->assertResponseErrorMessage(
            $res,
            __('exceptions.gps_device.subscription.not_restore'),
            400
        );
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsCarrierAccountant();

        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $subscription = $this->deviceSubscriptionsBuilder->company($model)->create();

        $this->putJson(route('gps.subscription.restore', [$subscription]))
            ->assertForbidden();
    }
}
