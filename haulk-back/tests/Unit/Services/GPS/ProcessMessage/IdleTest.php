<?php

namespace Tests\Unit\Services\GPS\ProcessMessage;

use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Services\GPS\GPSDataService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\HistoryBuilder;
use Tests\Builders\Saas\GPS\MessageBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\TestCase;

class IdleTest extends TestCase
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
    protected GPSDataService $service;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps',
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
        $this->service = resolve(GPSDataService::class);
    }

    /** @test */
    public function set_idle_and_engin_off(): void
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

        $h_1 = $this->historyBuilder
            ->receivedAt($date->subSeconds(100))
            ->truck($truck)
            ->device($device)
            ->create();
        $truck->update(['last_gps_history_id' => $h_1->id]);

        // idle
        $msg_1 = $this->messageBuilder
            ->device($device)
            ->receivedAt($date->subSeconds(90))
            ->speed(0)
            ->engineOff(false)
            ->create();
        // engin_off
        $msg_2 = $this->messageBuilder
            ->device($device)
            ->receivedAt($date->subSeconds(85))
            ->speed(0)
            ->engineOff(true)
            ->create();
        $msg_3 = $this->messageBuilder
            ->device($device)
            ->receivedAt($date->subSeconds(80))
            ->speed(0)
            ->engineOff(true)
            ->create();
        $msg_4 = $this->messageBuilder
            ->device($device)
            ->receivedAt($date->subSeconds(75))
            ->speed(0)
            ->engineOff(true)
            ->create();
        $msg_5 = $this->messageBuilder
            ->device($device)
            ->receivedAt($date->subSeconds(70))
            ->speed(0)
            ->engineOff(true)
            ->create();
        // idle
        $msg_6 = $this->messageBuilder
            ->device($device)
            ->receivedAt($date->subSeconds(65))
            ->speed(0)
            ->engineOff(false)
            ->create();
        $msg_6_1 = $this->messageBuilder
            ->device($device)
            ->receivedAt($date->subSeconds(63))
            ->speed(0)
            ->engineOff(false)
            ->create();
        // driving
        $msg_7 = $this->messageBuilder
            ->device($device)
            ->receivedAt($date->subSeconds(60))
            ->speed(10)
            ->engineOff(false)
            ->create();

        $this->service->processMessages();


        dd(
            History::query()
                ->select(['event_type', 'event_duration', 'received_at', 'last_received_at', 'msg_count_for_duration'])
                ->where('truck_id', $truck->id)
                ->get()
                ->toArray()
        );

        $truck->refresh();

        $history = History::query()->where('truck_id', $truck->id)->latest('received_at')->first();

        $this->assertEquals($truck->last_gps_history_id, $history->id);

        $this->assertEquals($history->event_type, History::EVENT_IDLE);
        $this->assertNull($history->event_duration);
    }


    /** @test */
    public function set_idle_not_prev_rec(): void
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

        $this->messageBuilder->device($device)->speed(0)->engineOff(false)->create();

        $this->assertNull($truck->lastGPSHistory);

        $this->service->processMessages();

        $truck->refresh();

        $history = History::query()->where('truck_id', $truck->id)->latest('received_at')->first();

        $this->assertEquals($truck->last_gps_history_id, $history->id);

        $this->assertEquals($history->event_type, History::EVENT_IDLE);
        $this->assertNull($history->event_duration);
    }

    /** @test */
    public function set_idle(): void
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

        $this->messageBuilder->device($device)->speed(0)->engineOff(false)->create();

        $history_1 = $this->historyBuilder->truck($truck)
            ->eventType(History::EVENT_DRIVING)
            ->receivedAt($date->subSeconds(55))
            ->create()
        ;
        $history_2 = $this->historyBuilder->truck($truck)
            ->eventType(History::EVENT_DRIVING)
            ->receivedAt($date->subSeconds(50))
            ->create()
        ;

        $this->assertNull($truck->lastGPSHistory);

        $this->service->processMessages();

        $truck->refresh();

        $history = History::query()->where('truck_id', $truck->id)->latest('received_at')->first();

        $this->assertEquals($truck->last_gps_history_id, $history->id);

        $this->assertEquals($history->event_type, History::EVENT_IDLE);
        $this->assertEquals($history->event_duration, 50);
    }

    /** @test */
    public function set_idle_with_duration(): void
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
            ->receivedAt($date->subSeconds(20))
            ->create()
        ;

        $truck->update(['last_gps_history_id' => $history->id]);

        $this->messageBuilder->device($device)->speed(0)->engineOff(false)->create();

        $this->service->processMessages();

        $truck->refresh();

        $history = History::query()->where('truck_id', $truck->id)->get();

        $this->assertCount(1, $history);

        $this->assertEquals($truck->last_gps_history_id, $history[0]->id);

        $this->assertEquals($history[0]->event_type, History::EVENT_IDLE);
        $this->assertEquals(20, $history[0]->event_duration);
    }
}
