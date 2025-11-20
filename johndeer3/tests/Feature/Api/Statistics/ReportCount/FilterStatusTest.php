<?php

namespace Tests\Feature\Api\Statistics\ReportCount;

use App\Models\User\Role;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class FilterStatusTest extends TestCase
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
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $user = $this->userBuilder
            ->setRole(Role::query()->where('role', Role::ROLE_PS)->first())
            ->create();

        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::OPEN_EDIT)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::EDITED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::EDITED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::IN_PROCESS)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::VERIFY)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->year,
        ];

        $this->getJson(route('api.statistic.report.filter.status', $data))
            ->assertJson($this->structureSuccessResponse([
                ReportStatus::CREATED => ReportStatus::listWithTranslatedAlias()[ReportStatus::CREATED] . ' (3)',
                ReportStatus::OPEN_EDIT => ReportStatus::listWithTranslatedAlias()[ReportStatus::OPEN_EDIT] . ' (1)',
                ReportStatus::EDITED => ReportStatus::listWithTranslatedAlias()[ReportStatus::EDITED] . ' (2)',
                ReportStatus::IN_PROCESS => ReportStatus::listWithTranslatedAlias()[ReportStatus::IN_PROCESS] . ' (1)',
                ReportStatus::VERIFY => ReportStatus::listWithTranslatedAlias()[ReportStatus::VERIFY] . ' (2)',
            ]))
        ;
    }

    /** @test */
    public function success_last_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $user = $this->userBuilder
            ->setRole(Role::query()->where('role', Role::ROLE_PS)->first())
            ->create();

        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::OPEN_EDIT)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::EDITED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::EDITED)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::IN_PROCESS)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::VERIFY)->create();
        $this->reportBuilder->setStatus($user)->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
        ];

        $this->getJson(route('api.statistic.report.filter.status', $data))
            ->assertJson($this->structureSuccessResponse([
                ReportStatus::CREATED => ReportStatus::listWithTranslatedAlias()[ReportStatus::CREATED] . ' (0)',
                ReportStatus::OPEN_EDIT => ReportStatus::listWithTranslatedAlias()[ReportStatus::OPEN_EDIT] . ' (0)',
                ReportStatus::EDITED => ReportStatus::listWithTranslatedAlias()[ReportStatus::EDITED] . ' (0)',
                ReportStatus::IN_PROCESS => ReportStatus::listWithTranslatedAlias()[ReportStatus::IN_PROCESS] . ' (0)',
                ReportStatus::VERIFY => ReportStatus::listWithTranslatedAlias()[ReportStatus::VERIFY] . ' (0)',
            ]))
        ;
    }

    /** @test */
    public function fail_without_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();

        $data = [];

        $this->getJson(route('api.statistic.report.filter.status', $data))
            ->assertJson($this->structureErrorResponse(["The year field is required."]))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $user = $this->userBuilder
            ->setRole(Role::query()->where('role', Role::ROLE_PS)->first())
            ->create();
        $this->loginAsUser($user);

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->subYear(),
        ];

        $this->getJson(route('api.statistic.report.filter.status', $data))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->subYear(),
        ];

        $this->getJson(route('api.statistic.report.filter.status', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}





