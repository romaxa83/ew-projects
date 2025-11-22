<?php

namespace Tests\Unit\Services\GPS\ProcessMessage;

use App\Http\Controllers\Api\Helpers\DbConnections;
use App\Models\GPS\Alert;
use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
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

class ConnectionRestroreTest extends TestCase
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
    public function set_connection_restore(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asSuperAdmin()->company($company)->create();
        /** @var $device Device */
        $device = $this->deviceBuilder->company($company)->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->company($company)->device($device)->create();

        $history = $this->historyBuilder->truck($truck)
            ->eventType(History::EVENT_DRIVING)
            ->receivedAt($date->subSeconds(55))
            ->create()
        ;
        $alert = $this->alertBuilder->history($history)
            ->type(Alert::ALERT_DEVICE_CONNECTION_LOST)->create();

        $truck->update(['last_gps_history_id' => $history->id]);

        $this->messageBuilder->device($device)->speed(100)->engineOff(false)->create();

        $this->service->processMessages();

        $truck->refresh();

        $history = History::query()->where('truck_id', $truck->id)
            ->latest('received_at')->first();

        $this->assertEquals($truck->last_gps_history_id, $history->id);

        $this->assertEquals($history->event_type, History::EVENT_DRIVING);
        $this->assertNull($history->event_duration);

        $this->assertCount(3, $history->alerts);

        $this->assertTrue($history->hasConnectionRestoreAlerts());
    }

    /** @test */
    public function not_set_connection_restore(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $user User */
        $user = $this->userBuilder->asSuperAdmin()->company($company)->create();
        /** @var $device Device */
        $device = $this->deviceBuilder->company($company)->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->company($company)->device($device)->create();

        $this->messageBuilder->device($device)->speed(100)->engineOff(false)->create();

        $this->service->processMessages();

        $truck->refresh();

        $history = History::query()->where('truck_id', $truck->id)
            ->latest('received_at')->first();

        $this->assertEquals($truck->last_gps_history_id, $history->id);

        $this->assertEquals($history->event_type, History::EVENT_DRIVING);
        $this->assertNull($history->event_duration);

        $this->assertFalse($history->hasConnectionRestoreAlerts());
    }
}




