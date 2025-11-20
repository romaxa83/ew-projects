<?php

namespace Tests\Feature\Api\Report;

use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UpdateTest extends TestCase
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

        /** @var $modelDescriptions ModelDescription */
        $modelDescriptions = ModelDescription::query()->first();
        $modelDescriptionsNew = ModelDescription::query()->where('id', 130)->first();

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)
            ->setModelDescription($modelDescriptions)
            ->create();

        $this->assertNotEquals($report->reportMachines[0]->model_description_id, $modelDescriptionsNew->id);

        $data = $this->data();
        $data['machines'][] = [
            'equipment_group_id' => $modelDescriptionsNew->equipmentGroup->id,
            'model_description_id' => $modelDescriptionsNew->id
        ];

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data)
        ;

        $report->refresh();

        $this->assertEquals($report->reportMachines[0]->model_description_id, $modelDescriptionsNew->id);
    }

    /** @test */
    public function fail_report_is_not_open_edit()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setStatus(ReportStatus::CREATED)
            ->setUser($user)
            ->create();

        $this->assertTrue($report->isCreated());

        $data = $this->data();

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data)
            ->assertJson($this->structureErrorResponse(
                __('message.report_not_open_for_edit')
            ))
        ;
    }

    /** @test */
    public function fail_report_is_not_owner()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $user1 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setStatus(ReportStatus::OPEN_EDIT)
            ->setUser($user1)
            ->create();

        $this->assertTrue($report->isOpenEdit());

        $data = $this->data();

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data)
            ->assertJson($this->structureErrorResponse(
                __('message.report_not_edit')
            ))
        ;
    }

    public function data(): array
    {
        return [
            'status' => ReportStatus::IN_PROCESS
        ];
    }
}



