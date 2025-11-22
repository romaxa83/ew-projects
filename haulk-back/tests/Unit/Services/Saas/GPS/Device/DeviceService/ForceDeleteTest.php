<?php

namespace Tests\Unit\Services\Saas\GPS\Device\DeviceService;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\GPS\Alert;
use App\Models\GPS\History;
use App\Models\Saas\GPS\Device;
use App\Services\Saas\GPS\Devices\DeviceService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\AlertBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\TestCase;

class ForceDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected HistoryBuilder $historyBuilder;
    protected DeviceService $service;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
        $this->alertBuilder = resolve(AlertBuilder::class);

        $this->service = resolve(DeviceService::class);
    }

    /** @test */
    public function success_force_delete(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $device_1 Device */
        $device_1 = $this->deviceBuilder->create();
        $d_1_id = $device_1->id;
        $device_2 = $this->deviceBuilder
            ->status(DeviceStatus::DELETED())
            ->create();
        $d_2_id = $device_2->id;
        // must delete
        $device_3 = $this->deviceBuilder
            ->status(DeviceStatus::DELETED(), $date->subDays(Device::DAYS_TO_FORCE_DELETE + 1))
            ->create();
        $d_3_id = $device_3->id;

        $h_1 = $this->historyBuilder->device($device_1)->create();
        $h_1_id = $h_1->id;
        $h_2 = $this->historyBuilder->device($device_2)->create();
        $h_2_id = $h_2->id;
        $h_3 = $this->historyBuilder->device($device_3)->create();
        $h_3_id = $h_3->id;
        $h_4 = $this->historyBuilder->device($device_3)->create();
        $h_4_id = $h_4->id;

        $a_1 = $this->alertBuilder->history($h_2)->create();
        $a_1_id = $a_1->id;
        $a_2 = $this->alertBuilder->history($h_3)->create();
        $a_2_id = $a_2->id;
        $a_3 = $this->alertBuilder->history($h_4)->create();
        $a_3_id = $a_3->id;

        $this->service->forceDelete();

        $this->assertTrue(Device::query()->withTrashed()->where('id', $d_1_id)->exists());
        $this->assertTrue(Device::query()->withTrashed()->where('id', $d_2_id)->exists());
        $this->assertFalse(Device::query()->withTrashed()->where('id', $d_3_id)->exists());

        $this->assertTrue(History::query()->where('id', $h_1_id)->exists());
        $this->assertTrue(History::query()->where('id', $h_2_id)->exists());
        $this->assertFalse(History::query()->where('id', $h_3_id)->exists());
        $this->assertFalse(History::query()->where('id', $h_4_id)->exists());

        $this->assertTrue(Alert::query()->where('id', $a_1_id)->exists());
        $this->assertFalse(Alert::query()->where('id', $a_2_id)->exists());
        $this->assertFalse(Alert::query()->where('id', $a_3_id)->exists());
    }
}



