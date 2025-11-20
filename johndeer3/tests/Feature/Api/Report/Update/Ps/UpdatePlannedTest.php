<?php

namespace Tests\Feature\Api\Report\Update\Ps;

use App\Helpers\DateFormat;
use App\Helpers\ReportHelper;
use App\Models\JD\Manufacturer;
use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UpdatePlannedTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)
            ->create();

        $this->assertNull($rep->pushData);

        $now = CarbonImmutable::now();
        $date = $now->timestamp * 1000;

        $data['planned_at'] = $date;

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "planned_at" => $data['planned_at']
                ]
            ])
        ;

        $rep->refresh();

        $this->assertEquals($rep->pushData->planned_at, $now->format('Y-m-d H:i:s'));
        $this->assertNull($rep->pushData->prev_planned_at);
        $this->assertFalse($rep->pushData->is_send_start_day);
        $this->assertFalse($rep->pushData->is_send_end_day);
        $this->assertFalse($rep->pushData->is_send_week);
    }

    /** @test */
    public function success_exist_push_data_but_not_have_planned_date()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData(['is_send_end_day' => true])
            ->setUser($user)
            ->create();

        $this->assertNotNull($rep->pushData);
        $this->assertNull($rep->pushData->planned_at);

        $now = CarbonImmutable::now();
        $date = $now->timestamp * 1000;

        $data['planned_at'] = $date;

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "planned_at" => $data['planned_at']
                ]
            ])
        ;

        $rep->refresh();

        $this->assertEquals($rep->pushData->planned_at, $now->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function success_planned_date_sub()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $dateSub = Carbon::now()->subDay();
        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData([
                'planned_at' => $dateSub,
                'is_send_start_day' => true,
                'is_send_end_day' => true,
                'is_send_week' => true
            ])
            ->setUser($user)
            ->create();

        $this->assertEquals($rep->pushData->planned_at, $dateSub->format('Y-m-d H:i:s'));
        $this->assertNull($rep->pushData->prev_planned_at);
        $this->assertTrue($rep->pushData->is_send_start_day);
        $this->assertTrue($rep->pushData->is_send_end_day);
        $this->assertTrue($rep->pushData->is_send_week);

        $now = CarbonImmutable::now();
        $date = $now->timestamp * 1000;

        $data['planned_at'] = $date;

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "planned_at" => $data['planned_at']
                ]
            ])
        ;

        $rep->refresh();

        $this->assertEquals($rep->pushData->planned_at, $now->format('Y-m-d H:i:s'));
        $this->assertEquals($rep->pushData->prev_planned_at, $dateSub->format('Y-m-d H:i:s'));
        $this->assertFalse($rep->pushData->is_send_start_day);
        $this->assertFalse($rep->pushData->is_send_end_day);
        $this->assertFalse($rep->pushData->is_send_week);
    }

    /** @test */
    public function success_planned_date_add()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $dateAdd = Carbon::now()->addDay();
        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData([
                'planned_at' => $dateAdd,
                'is_send_start_day' => true,
                'is_send_end_day' => true,
                'is_send_week' => true
            ])
            ->setUser($user)
            ->create();

        $this->assertEquals($rep->pushData->planned_at, $dateAdd->format('Y-m-d H:i:s'));
        $this->assertNull($rep->pushData->prev_planned_at);
        $this->assertTrue($rep->pushData->is_send_start_day);
        $this->assertTrue($rep->pushData->is_send_end_day);
        $this->assertTrue($rep->pushData->is_send_week);

        $now = CarbonImmutable::now();
        $date = $now->timestamp * 1000;

        $data['planned_at'] = $date;

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "planned_at" => $data['planned_at']
                ]
            ])
        ;

        $rep->refresh();

        $this->assertEquals($rep->pushData->planned_at, $now->format('Y-m-d H:i:s'));
        $this->assertEquals($rep->pushData->prev_planned_at, $dateAdd->format('Y-m-d H:i:s'));
        $this->assertFalse($rep->pushData->is_send_start_day);
        $this->assertFalse($rep->pushData->is_send_end_day);
        $this->assertFalse($rep->pushData->is_send_week);
    }

    /** @test */
    public function success_not_update()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $date = Carbon::now()->addDay();
        /** @var $rep Report */
        $rep = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setPushData([
                'planned_at' => $date,
                'is_send_start_day' => true,
                'is_send_end_day' => true,
                'is_send_week' => true
            ])
            ->setUser($user)
            ->create();

        $this->assertEquals($rep->pushData->planned_at, $date->format('Y-m-d H:i:s'));
        $this->assertNull($rep->pushData->prev_planned_at);
        $this->assertTrue($rep->pushData->is_send_start_day);
        $this->assertTrue($rep->pushData->is_send_end_day);
        $this->assertTrue($rep->pushData->is_send_week);

        $data['planned_at'] = $date->timestamp * 1000;

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), $data)
            ->assertJson([
                "data" => [
                    "id" => $rep->id,
                    "planned_at" => $data['planned_at']
                ]
            ])
        ;

        $rep->refresh();

        $this->assertEquals($rep->pushData->planned_at, $date->format('Y-m-d H:i:s'));
        $this->assertNull($rep->pushData->prev_planned_at);
        $this->assertTrue($rep->pushData->is_send_start_day);
        $this->assertTrue($rep->pushData->is_send_end_day);
        $this->assertTrue($rep->pushData->is_send_week);
    }
}
