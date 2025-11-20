<?php

namespace Tests\Unit\Console\FcmNotifications;

use App\Events\FcmPushGroup;
use App\Models\Notification\FcmTemplate;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;

class PushNotyInDayStartTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success_template_planed(): void
    {
        \Event::fake([FcmPushGroup::class]);

        $date = Carbon::now()->addDay();

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->create();

        $this->assertFalse($rep_1->pushData->is_send_start_day);
        $this->assertNull($rep_1->pushData->prev_planned_at);

        $this->artisan('jd:report:push-in-start-day')
            ->assertExitCode(0)
        ;

        $rep_1->refresh();

        $this->assertTrue($rep_1->pushData->is_send_start_day);

        \Event::assertDispatched(FcmPushGroup::class, function ($event) use ($rep_1){
            return $event->report->id === $rep_1->id && $event->templateName == FcmTemplate::PLANNED;
        });
    }

    /** @test */
    public function success_template_postponed(): void
    {
        \Event::fake([FcmPushGroup::class]);

        $date = Carbon::now()->addDay();

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData([
                'planned_at' => $date,
                'prev_planned_at' => Carbon::now(),
            ])->create();

        $this->assertFalse($rep_1->pushData->is_send_start_day);
        $this->assertNotNull($rep_1->pushData->prev_planned_at);

        $this->artisan('jd:report:push-in-start-day')
            ->assertExitCode(0)
        ;

        $rep_1->refresh();

        $this->assertTrue($rep_1->pushData->is_send_start_day);

        \Event::assertDispatched(FcmPushGroup::class, function ($event) use ($rep_1){
            return $event->report->id === $rep_1->id && $event->templateName == FcmTemplate::POSTPONED;
        });
    }

    /** @test */
    public function success_some_report(): void
    {
        \Event::fake([FcmPushGroup::class]);

        $date = Carbon::now()->addDay();

        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->create();
        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->create();
        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['planned_at' => $date])->create();

        $this->artisan('jd:report:push-in-start-day')
            ->assertExitCode(0)
        ;

        \Event::assertDispatchedTimes(FcmPushGroup::class, 3);
    }

    /** @test */
    public function fail_already_sent(): void
    {
        \Event::fake([FcmPushGroup::class]);

        $date = Carbon::now()->addDay();

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData([
                'planned_at' => $date,
                "is_send_start_day" => true
            ])->create();

        $this->assertTrue($rep_1->pushData->is_send_start_day);

        $this->artisan('jd:report:push-in-start-day')
            ->assertExitCode(0)
        ;

        \Event::assertNotDispatched(FcmPushGroup::class);
    }

    /** @test */
    public function fail_old_report(): void
    {
        \Event::fake([FcmPushGroup::class]);

        $date = Carbon::now()->subDay();

        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData([
                'planned_at' => $date
            ])->create();

        $this->artisan('jd:report:push-in-start-day')
            ->assertExitCode(0)
        ;

        \Event::assertNotDispatched(FcmPushGroup::class);
    }

    /** @test */
    public function fail_too_early(): void
    {
        \Event::fake([FcmPushGroup::class]);

        $date = Carbon::now()->addHours(40);

        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData([
                'planned_at' => $date
            ])->create();

        $this->artisan('jd:report:push-in-start-day')
            ->assertExitCode(0)
        ;

        \Event::assertNotDispatched(FcmPushGroup::class);
    }
}

