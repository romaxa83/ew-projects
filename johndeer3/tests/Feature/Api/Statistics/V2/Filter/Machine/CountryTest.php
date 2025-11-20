<?php

namespace Tests\Feature\Api\Statistics\V2\Filter\Machine;

use App\Models\User\Role;
use App\Services\Statistics\StatisticFilterService;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class CountryTest extends TestCase
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

        list($ukraine, $poland, $uk) = ['Ukraine', 'Poland', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($poland)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->create();

        $data = [
            'year' => Carbon::now()->year,
        ];

        $this->getJson(route('api.v2.statistic.filter.country', $data))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk . ' (2)',
                $poland => $poland . ' (1)',
                $ukraine => $ukraine . ' (2)',
            ]))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_last_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $year = Carbon::now()->subYear();

        list($ukraine, $poland, $uk) = ['Ukraine', 'Poland', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setCreatedAt($year)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($poland)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->create();

        $data = [
            'year' => $year->year,
        ];

        $this->getJson(route('api.v2.statistic.filter.country', $data))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk . ' (0)',
                $poland => $poland . ' (0)',
                $ukraine => $ukraine . ' (1)',
            ]))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_different_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        list($ukraine, $poland, $uk) = ['Ukraine', 'Poland', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->create();
        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setCountry($ukraine)->create();
        $this->reportBuilder->setStatus(ReportStatus::VERIFY)
            ->setCountry($ukraine)->create();
        $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)
            ->setCountry($ukraine)->create();
        $this->reportBuilder->setStatus(ReportStatus::EDITED)
            ->setCountry($ukraine)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($poland)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->create();

        $data = [
            'year' => Carbon::now()->year,
        ];

        $this->getJson(route('api.v2.statistic.filter.country', $data))
            ->assertJson($this->structureSuccessResponse([
                $uk => $uk . ' (2)',
                $poland => $poland . ' (1)',
                $ukraine => $ukraine . ' (3)',
            ]))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $data = [
            'year' => Carbon::now()->year
        ];

        $this->getJson(route('api.v2.statistic.filter.country', $data))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_wrong()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        list($ukraine) = ['Ukraine'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->create();

        $data = [
            'year' => 'wrong',
        ];

        $this->getJson(route('api.v2.statistic.filter.country', $data))
            ->assertJson($this->structureSuccessResponse([
                $ukraine => $ukraine . ' (0)',
            ]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function fail_without_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        list($ukraine) = ['Ukraine'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->create();

        $data = [];

        $this->getJson(route('api.v2.statistic.filter.country', $data))
            ->assertJson($this->structureErrorResponse(["The year field is required."]))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(StatisticFilterService::class, function(MockInterface $mock){
            $mock->shouldReceive("machineCountryData")
                ->andThrows(\Exception::class, "some exception message");
        });

        $data = [
            'year' => Carbon::now()->year
        ];

        $this->getJson(route('api.v2.statistic.filter.country', $data))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $admin = $this->userBuilder
            ->setRole(Role::query()->where('role', Role::ROLE_PS)->first())
            ->create();
        $this->loginAsUser($admin);

        $data = [
            'year' => Carbon::now()->year,
        ];

        $this->getJson(route('api.v2.statistic.filter.country', $data))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $data = [
            'year' => Carbon::now()->year,
        ];

        $this->getJson(route('api.v2.statistic.filter.country', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
