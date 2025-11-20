<?php

namespace Tests\Feature\Api\Statistics\ReportCount;

use App\Models\JD\Dealer;
use App\Models\User\Role;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class FilterCountryTest extends TestCase
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

    // todo - написать тест -
    // 1. коректность статусов
    // 2. получение по статусу all

    /** @test */
    public function success()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR);

        $de = "DE - German";
        $dealerDE = Dealer::query()->where('country', $de)->first();
        $this->assertNotNull($dealerDE);

        $userFR = $this->userBuilder->setDealer($dealerFR)
            ->setRole($role)->create();

        $userDE = $this->userBuilder->setDealer($dealerDE)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userDE)
            ->setStatus(ReportStatus::CREATED)->create();

        // report not check
        $this->reportBuilder->setUser($userDE)
            ->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED
        ];

        $this->getJson(route('api.statistic.report.filter.country', $data))
            ->assertJson($this->structureSuccessResponse([
                $de => 'German (1)',
                $fr => 'French (2)'
            ]))
        ;
    }

    /** @test */
    public function success_last_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR);

        $userFR = $this->userBuilder->setDealer($dealerFR)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'status' => ReportStatus::CREATED
        ];

        $this->getJson(route('api.statistic.report.filter.country', $data))
            ->assertJson($this->structureSuccessResponse([]))
        ;
    }

    /** @test */
    public function success_some_statuses()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR);

        $de = "DE - German";
        $dealerDE = Dealer::query()->where('country', $de)->first();
        $this->assertNotNull($dealerDE);

        $userFR = $this->userBuilder->setDealer($dealerFR)
            ->setRole($role)->create();

        $userDE = $this->userBuilder->setDealer($dealerDE)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userDE)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userDE)
            ->setStatus(ReportStatus::VERIFY)->create();

        // report not check
        $this->reportBuilder->setUser($userDE)
            ->setStatus(ReportStatus::IN_PROCESS)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED .','.ReportStatus::VERIFY
        ];

        $this->getJson(route('api.statistic.report.filter.country', $data))
            ->assertJson($this->structureSuccessResponse([
                $de => 'German (2)',
                $fr => 'French (2)'
            ]))
        ;
    }

    /** @test */
    public function fail_without_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR);

        $userFR = $this->userBuilder->setDealer($dealerFR)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'status' => ReportStatus::CREATED,
        ];

        $this->getJson(route('api.statistic.report.filter.country', $data))
            ->assertJson($this->structureErrorResponse(['The year field is required.']))
        ;
    }

    /** @test */
    public function fail_without_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR);

        $userFR = $this->userBuilder->setDealer($dealerFR)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
        ];

        $this->getJson(route('api.statistic.report.filter.country', $data))
            ->assertJson($this->structureErrorResponse(['The status field is required.']))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR);

        $userFR = $this->userBuilder->setDealer($dealerFR)
            ->setRole($role)->create();
        $this->loginAsUser($userFR);

        // report check
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
        ];

        $this->getJson(route('api.statistic.report.filter.country', $data))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR);

        $userFR = $this->userBuilder->setDealer($dealerFR)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED
        ];

        $this->getJson(route('api.statistic.report.filter.status', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}






