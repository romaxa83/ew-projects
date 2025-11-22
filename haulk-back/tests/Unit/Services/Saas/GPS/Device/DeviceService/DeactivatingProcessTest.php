<?php

namespace Tests\Unit\Services\Saas\GPS\Device\DeviceService;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Enums\Saas\GPS\DeviceStatusActivateRequest;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Models\Vehicles\Truck;
use App\Services\Saas\GPS\Devices\DeviceService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class DeactivatingProcessTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;

    protected DeviceService $service;

    public array $connectionsToTransact = [
        'pgsql'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);

        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);

        $this->service = resolve(DeviceService::class);
    }

    /** @test */
    public function process(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $device_1 Device */
        $device_1 = $this->deviceBuilder->status(DeviceStatus::ACTIVE())->create();

        $device_2 = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->statusActiveRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->activeTillAt($date->subHour())
            ->create();
        $device_3 = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->statusActiveRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->activeTillAt($date->subHour())
            ->create();
        $device_4 = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->statusActiveRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->activeTillAt($date->addHour())
            ->create();
        $device_5 = $this->deviceBuilder
            ->status(DeviceStatus::INACTIVE())
            ->create();

        /** @var $truck_1 Truck */
        $truck_1 = $this->truckBuilder->device($device_1)->create();
        $truck_2 = $this->truckBuilder->device($device_2)->create();
        $truck_3 = $this->truckBuilder->device($device_4)->create();

        $trailer_1 =$this->trailerBuilder->device($device_3)->create();

        $this->service->deactivatingProcess();

        $device_1->refresh();
        $device_2->refresh();
        $device_3->refresh();
        $device_4->refresh();
        $device_5->refresh();
        $truck_1->refresh();
        $truck_2->refresh();
        $truck_3->refresh();
        $trailer_1->refresh();

        $this->assertTrue($device_1->status->isActive());

        $this->assertTrue($device_2->status->isInactive());
        $this->assertTrue($device_2->status_activate_request->isNone());
        $this->assertNull($device_2->active_till_at);
        $this->assertNotNull($device_2->inactive_at);

        $this->assertTrue($device_3->status->isInactive());
        $this->assertTrue($device_3->status_activate_request->isNone());
        $this->assertNull($device_3->active_till_at);
        $this->assertNotNull($device_3->inactive_at);

        $this->assertTrue($device_4->status->isActive());
        $this->assertTrue($device_4->status_activate_request->isDeactivate());
        $this->assertNotNull($device_4->active_till_at);
        $this->assertNull($device_4->inactive_at);

        $this->assertTrue($device_5->status->isInactive());

        $this->assertNotNull($truck_1->gps_device_id);
        $this->assertNull($truck_2->gps_device_id);
        $this->assertNotNull($truck_3->gps_device_id);
        $this->assertNull($trailer_1->gps_device_id);

    }

    /** @test */
    public function process_active_till_subscription(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE())
            ->company($company)->create();

        $device_2 = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->statusActiveRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->activeTillAt($date->subHour())
            ->company($company)
            ->create();
        $device_3 = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->statusActiveRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->activeTillAt($date->subHour())
            ->company($company)
            ->create();
        $device_5 = $this->deviceBuilder
            ->status(DeviceStatus::INACTIVE())
            ->company($company)
            ->create();

        $this->service->deactivatingProcess();

        $subscription->refresh();

        $this->assertTrue($subscription->status->isActiveTill());
    }

    /** @test */
    public function process_not_active_till_subscription(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->trial($this->companyBuilder->create());

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE())
            ->company($company)->create();

        $device_2 = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->statusActiveRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->activeTillAt($date->subHour())
            ->company($company)
            ->create();
        $device_3 = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->statusActiveRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->activeTillAt($date->subHour())
            ->company($company)
            ->create();
        $device_4 = $this->deviceBuilder
            ->status(DeviceStatus::ACTIVE())
            ->statusActiveRequest(DeviceStatusActivateRequest::DEACTIVATE())
            ->activeTillAt($date->addHour())
            ->company($company)
            ->create();
        $device_5 = $this->deviceBuilder
            ->status(DeviceStatus::INACTIVE())
            ->company($company)
            ->create();

        $this->service->deactivatingProcess();

        $subscription->refresh();

        $this->assertTrue($subscription->status->isActive());

    }
}




