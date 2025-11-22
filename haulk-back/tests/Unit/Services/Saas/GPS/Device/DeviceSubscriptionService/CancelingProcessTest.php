<?php

namespace Tests\Unit\Services\Saas\GPS\Device\DeviceSubscriptionService;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Services\Saas\GPS\Devices\DeviceSubscriptionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class CancelingProcessTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected DeviceSubscriptionService $service;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;

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

        $this->service = resolve(DeviceSubscriptionService::class);
    }

    /** @test */
    public function process(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->activateTillAt($date->subHour())
            ->company($company)->create();

        /** @var $d_1 Device */
        $d_1 = $this->deviceBuilder->company($company)->status(DeviceStatus::ACTIVE())->create();
        $d_2 = $this->deviceBuilder->company($company)->status(DeviceStatus::ACTIVE())->create();
        $d_3 = $this->deviceBuilder->company($company)->status(DeviceStatus::INACTIVE())->create();
        $d_4 = $this->deviceBuilder->company($company)->status(DeviceStatus::DELETED())->create();

        $truck_1 = $this->truckBuilder->device($d_1)->create();
        $truck_2 = $this->truckBuilder->device($d_3)->create();
        $trailer_1 = $this->trailerBuilder->device($d_2)->create();

        $this->service->cancelingProcess();

        $subscription->refresh();

        $this->assertEquals($subscription->canceled_at->timestamp, $date->timestamp);
        $this->assertNull($subscription->activate_till_at);
        $this->assertTrue($subscription->status->isCanceled());

        $d_1->refresh();
        $d_2->refresh();
        $d_3->refresh();
        $d_4->refresh();

        $this->assertTrue($d_1->status->isInactive());
        $this->assertEquals($d_1->inactive_at->timestamp, $date->timestamp);
        $this->assertTrue($d_2->status->isInactive());
        $this->assertEquals($d_2->inactive_at->timestamp, $date->timestamp);
        $this->assertTrue($d_3->status->isInactive());
        $this->assertEquals($d_3->inactive_at->timestamp, $date->timestamp);
        $this->assertTrue($d_4->status->isDeleted());


        $this->assertEquals($d_1->histories[0]->context, DeviceHistoryContext::INACTIVE);
        $this->assertEquals($d_2->histories[0]->context, DeviceHistoryContext::INACTIVE);
        $this->assertEquals($d_3->histories[0]->context, DeviceHistoryContext::INACTIVE);
        $this->assertEmpty($d_4->histories);

        $truck_1->refresh();
        $truck_2->refresh();
        $trailer_1->refresh();

        $this->assertNull($truck_1->gps_device_id);
        $this->assertNull($truck_2->gps_device_id);
        $this->assertNull($trailer_1->gps_device_id);
    }
}
