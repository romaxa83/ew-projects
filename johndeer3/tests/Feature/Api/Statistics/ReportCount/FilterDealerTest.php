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

class FilterDealerTest extends TestCase
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

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $dealerFR_2 = Dealer::query()->where('country', $fr)->where('id', '!=', $dealerFR_1->id)->first();
        $this->assertNotNull($dealerFR_2);

        $de = "DE - German";
        $dealerDE = Dealer::query()->where('country', $de)->first();
        $this->assertNotNull($dealerDE);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_1_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_2 = $this->userBuilder->setDealer($dealerFR_2)
            ->setRole($role)->create();

        $userDE = $this->userBuilder->setDealer($dealerDE)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $this->reportBuilder->setUser($userFR_2)
            ->setStatus(ReportStatus::CREATED)->create();

        // report not check
        $this->reportBuilder->setUser($userDE)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_2)
            ->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr
        ];

        $this->getJson(route('api.statistic.report.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealerFR_1->id => $dealerFR_1->name . ' (3)',
                $dealerFR_2->id => $dealerFR_2->name . ' (1)',
            ]))
        ;
    }

    /** @test */
    public function success_some_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $dealerFR_2 = Dealer::query()->where('country', $fr)->where('id', '!=', $dealerFR_1->id)->first();
        $this->assertNotNull($dealerFR_2);

        $de = "DE - German";
        $dealerDE = Dealer::query()->where('country', $de)->first();
        $this->assertNotNull($dealerDE);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_1_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_2 = $this->userBuilder->setDealer($dealerFR_2)
            ->setRole($role)->create();

        $userDE = $this->userBuilder->setDealer($dealerDE)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $this->reportBuilder->setUser($userFR_2)
            ->setStatus(ReportStatus::CREATED)->create();

        $this->reportBuilder->setUser($userDE)
            ->setStatus(ReportStatus::CREATED)->create();

        // report not check
        $this->reportBuilder->setUser($userFR_2)
            ->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED,
            'country' => $fr.','.$de
        ];

        $this->getJson(route('api.statistic.report.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealerFR_1->id => $dealerFR_1->name . ' (3)',
                $dealerFR_2->id => $dealerFR_2->name . ' (1)',
                $dealerDE->id => $dealerDE->name . ' (1)',
            ]))
        ;
    }

    /** @test */
    public function success_some_country_and_some_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $dealerFR_2 = Dealer::query()->where('country', $fr)->where('id', '!=', $dealerFR_1->id)->first();
        $this->assertNotNull($dealerFR_2);

        $de = "DE - German";
        $dealerDE = Dealer::query()->where('country', $de)->first();
        $this->assertNotNull($dealerDE);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_1_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();
        $userFR_2 = $this->userBuilder->setDealer($dealerFR_2)
            ->setRole($role)->create();

        $userDE = $this->userBuilder->setDealer($dealerDE)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $this->reportBuilder->setUser($userFR_2)
            ->setStatus(ReportStatus::CREATED)->create();

        $this->reportBuilder->setUser($userDE)
            ->setStatus(ReportStatus::CREATED)->create();

        $this->reportBuilder->setUser($userFR_2)
            ->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->year,
            'status' => ReportStatus::CREATED.','.ReportStatus::VERIFY,
            'country' => $fr.','.$de
        ];

        $this->getJson(route('api.statistic.report.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealerFR_1->id => $dealerFR_1->name . ' (3)',
                $dealerFR_2->id => $dealerFR_2->name . ' (2)',
                $dealerDE->id => $dealerDE->name . ' (1)',
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
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::VERIFY)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'status' => ReportStatus::CREATED.','.ReportStatus::VERIFY,
            'country' => $fr
        ];

        $this->getJson(route('api.statistic.report.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([]))
        ;
    }

    /** @test */
    public function fail_without_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'status' => ReportStatus::CREATED,
            'country' => $fr
        ];

        $this->getJson(route('api.statistic.report.filter.dealer', $data))
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
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'country' => $fr
        ];

        $this->getJson(route('api.statistic.report.filter.dealer', $data))
            ->assertJson($this->structureErrorResponse(['The status field is required.']))
        ;
    }

    /** @test */
    public function fail_without_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'status' => ReportStatus::CREATED,
        ];

        $this->getJson(route('api.statistic.report.filter.dealer', $data))
            ->assertJson($this->structureErrorResponse(['The country field is required.']))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        $this->loginAsUser($userFR_1);

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->subYear(),
            'status' => ReportStatus::CREATED,
            'country' => $fr
        ];

        $this->getJson(route('api.statistic.report.filter.dealer', $data))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access'))) ->assertJson($this->structureErrorResponse("button (end)"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $fr = "FR - French";
        $dealerFR_1 = Dealer::query()->where('country', $fr)->first();
        $this->assertNotNull($dealerFR_1);

        $userFR_1 = $this->userBuilder->setDealer($dealerFR_1)
            ->setRole($role)->create();

        // report check
        $this->reportBuilder->setUser($userFR_1)
            ->setStatus(ReportStatus::CREATED)->create();

        $data = [
            'year' => Carbon::now()->subYear(),
            'status' => ReportStatus::CREATED,
            'country' => $fr
        ];

        $this->getJson(route('api.statistic.report.filter.dealer', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}







