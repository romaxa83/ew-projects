<?php

namespace Tests\Unit\Services\Saas\GPS\Device\DeviceService;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceRequestStatus;
use App\Models\Saas\GPS\Device;
use App\Services\Saas\GPS\Devices\DeviceService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\TestCase;

class RemoveClosedDeviceRequestStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceBuilder $deviceBuilder;
    protected HistoryBuilder $historyBuilder;
    protected DeviceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);

        $this->service = resolve(DeviceService::class);
    }

    /** @test */
    public function success_remove_status(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $device_1 Device */
        $device_1 = $this->deviceBuilder
            ->statusRequest(DeviceRequestStatus::CLOSED())
            ->requestClosedAt($date->subHours(25))
            ->create();

        $device_2 = $this->deviceBuilder
            ->statusRequest(DeviceRequestStatus::CLOSED())
            ->requestClosedAt($date->subHours(20))
            ->create();

        $this->service->removeClosedDeviceRequestStatus();

        $device_1->refresh();
        $device_2->refresh();

        $this->assertTrue($device_1->status_request->isNone());
        $this->assertNull($device_1->request_closed_at);

        $this->assertTrue($device_2->status_request->isClosed());
        $this->assertNotNull($device_2->request_closed_at);

        $this->assertEquals($device_1->histories[0]->context, DeviceHistoryContext::REMOVE_REQUEST_CLOSED());
    }
}




