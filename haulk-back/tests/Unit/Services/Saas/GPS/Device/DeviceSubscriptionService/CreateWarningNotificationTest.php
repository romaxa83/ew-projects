<?php

namespace Tests\Unit\Services\Saas\GPS\Device\DeviceSubscriptionService;

use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Notifications\Notification;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Services\Saas\GPS\Devices\DeviceSubscriptionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\TestCase;

class CreateWarningNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected DeviceSubscriptionService $service;

    public array $connectionsToTransact = [
        'pgsql'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);

        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);

        $this->service = resolve(DeviceSubscriptionService::class);
    }

    /** @test */
    public function process(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $this->assertFalse(Notification::query()->where('created_at', $date)->exists());

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE_TILL())
            ->activateTillAt($date)
            ->company($company)
            ->create();

        $this->service->createWarningNotification();

        $this->assertTrue(Notification::query()->where('created_at', $date)->exists());

        $subscription->refresh();

        $this->assertTrue($subscription->send_warning_notify);
    }

    /** @test */
    public function send_yet(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $this->assertFalse(Notification::query()->where('created_at', $date)->exists());

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE_TILL())
            ->activateTillAt($date->subDay())
            ->sendWarningNotify()
            ->company($company)
            ->create();

        $this->service->createWarningNotification();

        $this->assertFalse(Notification::query()->where('created_at', $date)->exists());
    }

    /** @test */
    public function not_send(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $this->assertFalse(Notification::query()->where('created_at', $date)->exists());

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE_TILL())
            ->activateTillAt($date->subDays(2))
            ->company($company)
            ->create();

        $this->service->createWarningNotification();

//        $this->assertFalse(Notification::query()->where('created_at', $date)->exists());

        $subscription->refresh();

        $this->assertFalse($subscription->send_warning_notify);
    }
}

