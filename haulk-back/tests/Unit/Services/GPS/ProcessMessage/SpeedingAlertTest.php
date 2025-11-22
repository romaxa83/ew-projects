<?php

namespace Tests\Unit\Services\GPS\ProcessMessage;

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\GPS\Alert;
use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Vehicles\Truck;
use App\Services\GPS\GPSDataService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\AlertBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\Builders\Saas\GPS\MessageBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\TestCase;

class SpeedingAlertTest extends TestCase
{
    use DatabaseTransactions;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected TruckBuilder $truckBuilder;
    protected UserBuilder $userBuilder;
    protected MessageBuilder $messageBuilder;
    protected HistoryBuilder $historyBuilder;
    protected AlertBuilder $alertBuilder;
    protected GPSDataService $service;

    public array $connectionsToTransact = [
        DbConnections::GPS, DbConnections::DEFAULT
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->messageBuilder = resolve(MessageBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
        $this->alertBuilder = resolve(AlertBuilder::class);
        $this->service = resolve(GPSDataService::class);
    }

    /** @test */
    public function set_speeding_alert(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $device Device */
        $device = $this->deviceBuilder->company($company)->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->company($company)->device($device)->create();

        $this->messageBuilder->device($device)->speed(100)->engineOff(false)->create();

        $this->assertFalse(History::query()->where('truck_id', $truck->id)->exists());
        $this->assertNull($truck->lastGPSHistory);

        $this->service->processMessages();

        $truck->refresh();

        $history = History::query()->where('truck_id', $truck->id)->first();

        $this->assertEquals($truck->last_gps_history_id, $history->id);

        $this->assertEquals($history->event_type, History::EVENT_DRIVING);
        $this->assertNull($history->event_duration);
        $this->assertTrue($history->is_speeding);

        $this->assertEquals($history->alerts[0]->alert_type, Alert::ALERT_TYPE_SPEEDING);
    }

    /** @test */
    public function next_recs_with_speeding(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $device Device */
        $device = $this->deviceBuilder->company($company)->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->company($company)->device($device)->create();

        $history = $this->historyBuilder->truck($truck)
            ->eventType(History::EVENT_IDLE)
            ->speed(99)
            ->isSpeeding(true)
            ->receivedAt($date->subSeconds(20))
            ->create()
        ;
        $alert = $this->alertBuilder->truck($truck)
            ->history($history)
            ->type(Alert::ALERT_TYPE_SPEEDING)
            ->create();


        $truck->update(['last_gps_history_id' => $history->id]);

        $this->messageBuilder->device($device)->speed(100)->engineOff(false)->create();

        $this->service->processMessages();

        $truck->refresh();

        $history = History::query()->where('truck_id', $truck->id)->get();

        $this->assertTrue($history[0]->is_speeding);
        $this->assertTrue($history[1]->is_speeding);
    }
}




