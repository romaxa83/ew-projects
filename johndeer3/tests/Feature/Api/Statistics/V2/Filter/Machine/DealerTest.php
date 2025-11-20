<?php

namespace Tests\Feature\Api\Statistics\V2\Filter\Machine;

use App\Models\JD\Dealer;
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

class DealerTest extends TestCase
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

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealer_1->id => $dealer_1->name . ' (2)',
                $dealer_2->id => $dealer_2->name . ' (1)'
            ]))
            ->assertJsonCount(2, 'data')
        ;

        // another request
        $data = [
            'year' => Carbon::now()->year,
            'country' => $uk
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealer_1->id => $dealer_1->name . ' (1)',
                $dealer_2->id => $dealer_2->name . ' (1)'
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_few_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($poland)->setUser($user_3)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => [$ukraine, $uk]
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealer_1->id => $dealer_1->name . ' (3)',
                $dealer_2->id => $dealer_2->name . ' (2)'
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_as_all()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($poland)->setUser($user_3)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => 'all'
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealer_1->id => $dealer_1->name . ' (3)',
                $dealer_2->id => $dealer_2->name . ' (3)'
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_last_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];

        $year = Carbon::now()->subYear();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setCreatedAt($year)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($poland)->setUser($user_3)->create();

        $data = [
            'year' => $year->year,
            'country' => 'all'
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealer_1->id => $dealer_1->name . ' (1)',
                $dealer_2->id => $dealer_2->name . ' (0)'
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_different_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $poland, $uk) = ['Ukraine', 'Poland', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::VERIFY)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::EDITED)
            ->setCountry($ukraine)->setUser($user_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($poland)->setUser($user_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => 'all'
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealer_1->id => $dealer_1->name . ' (4)',
            ]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            "country" => 'uk'
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_wrong()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $poland, $uk) = ['Ukraine', 'Poland', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => 'wrong'
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureSuccessResponse([
                $dealer_1->id => $dealer_1->name . ' (0)',
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

        $data = [
            'country' => 'wrong'
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureErrorResponse(["The year field is required."]))
        ;
    }

    /** @test */
    public function fail_without_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        list($ukraine) = ['Ukraine'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->create();

        $data = [
            'year' => Carbon::now()->year,
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureErrorResponse(["The country field is required."]))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(StatisticFilterService::class, function(MockInterface $mock){
            $mock->shouldReceive("machineDealerData")
                ->andThrows(\Exception::class, "some exception message");
        });

        $data = [
            'year' => Carbon::now()->year,
            'country' => 'uk'
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
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
            'country' => 'uk'
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $data = [
            'year' => Carbon::now()->year,
            'country' => 'uk'
        ];

        $this->getJson(route('api.v2.statistic.filter.dealer', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

