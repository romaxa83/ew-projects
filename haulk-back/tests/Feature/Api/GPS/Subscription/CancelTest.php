<?php

namespace Tests\Feature\Api\GPS\Subscription;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Notifications\Notification;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class CancelTest extends TestCase
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
    public function success_cancel(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $model Company */
        $model = $this->companyBuilder->trial($this->companyBuilder->create());

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE())
            ->company($model)->create();

        /** @var $d_1 Device */
        $d_1 = $this->deviceBuilder->company($model)
            ->status(DeviceStatus::ACTIVE())
            ->statusRequest(DeviceRequestStatus::PENDING())->create();
        $d_2 = $this->deviceBuilder->company($model)
            ->status(DeviceStatus::ACTIVE())
            ->statusRequest(DeviceRequestStatus::CLOSED())->create();
        $d_3 = $this->deviceBuilder->company($model)
            ->status(DeviceStatus::INACTIVE())
            ->statusRequest(DeviceRequestStatus::NONE())->create();

        $this->assertEquals($subscription->status, DeviceSubscriptionStatus::ACTIVE());
        $this->assertNull($subscription->active_till_at);
        $this->assertFalse(Notification::query()
            ->where('created_at',$date)
            ->exists()
        );

        $this->assertNull($d_1->active_till_at);
        $this->assertNull($d_2->active_till_at);
        $this->assertNull($d_3->active_till_at);

        $this->putJson(route('gps.subscription.cancel', [$subscription->id]))
            ->assertJson([
                'data' => [
                    'id' => $subscription->id,
                    'status' => DeviceSubscriptionStatus::ACTIVE_TILL(),
                    'active_till_at' => $model->subscription->billing_end->timestamp,
                ]
            ]);

        $this->assertTrue(Notification::query()
            ->where('created_at',$date)
            ->exists()
        );

        $d_1->refresh();
        $d_2->refresh();
        $d_3->refresh();

        $this->assertTrue($d_1->status_request->isCancelSubscription());
        $this->assertTrue($d_2->status_request->isCancelSubscription());
        $this->assertTrue($d_3->status_request->isCancelSubscription());

        $this->assertEquals($d_1->active_till_at->timestamp, $model->subscription->billing_end->timestamp);
        $this->assertEquals($d_2->active_till_at->timestamp, $model->subscription->billing_end->timestamp);
        $this->assertNull($d_3->active_till_at);

        $this->assertEquals($d_1->histories[0]->context, DeviceHistoryContext::SUBSCRIPTION_CANCEL());
        $this->assertEquals($d_2->histories[0]->context, DeviceHistoryContext::SUBSCRIPTION_CANCEL());
        $this->assertEquals($d_3->histories[0]->context, DeviceHistoryContext::SUBSCRIPTION_CANCEL());
    }

    /** @test */
    public function fail_not_has_subscription(): void
    {
        $this->loginAsCarrierSuperAdmin();

        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $subscription = $this->deviceSubscriptionsBuilder->company($model)->create();

        $res = $this->putJson(route('gps.subscription.cancel', ['id' => 9999]));

        $this->assertResponseErrorMessage(
            $res,
            __('exceptions.gps_device.subscription.not_active_subscription'),
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

        $this->putJson(route('gps.subscription.cancel', [$subscription]))
            ->assertForbidden();
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Company */
        $model = $this->companyBuilder->create();

        $this->putJson(route('v1.saas.companies.cancel-gps-subscription', [$model->id]))
            ->assertUnauthorized();
    }
}




