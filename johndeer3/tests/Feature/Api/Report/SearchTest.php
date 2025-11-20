<?php

namespace Tests\Feature\Api\Report;

use App\Models\JD\Client;
use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\Report\ReportRepository;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class SearchTest extends TestCase
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
    public function success_result_for_ps()
    {
        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('country', "DE - German")->first();

        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $this->loginAsUser($user_1);

        $eg_1 = EquipmentGroup::query()->first();

        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $rep_1 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_1)->create();
        $rep_2 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_1)->create();
        $rep_3 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_2)->create();

        $rep_4 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_3)->create();

        $this->getJson(route('api.report.search', ['search' => $eg_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
                ["id" => $rep_3->id],
            ]])
            ->assertJsonCount(3, 'data')
        ;
        // another ps
        $this->loginAsUser($user_3);

        $this->getJson(route('api.report.search', ['search' => $eg_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_4->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_result_for_sm()
    {
        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('country', "DE - German")->first();

        $role = Role::query()->where('role', Role::ROLE_SM)->first();
        $user_1_sm = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2_sm = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();
        $this->loginAsUser($user_1_sm);

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();

        $eg_1 = EquipmentGroup::query()->first();

        $user_2 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_2)->create();

        $rep_1 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_2)->create();
        $rep_2 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_2)->create();

        $rep_3 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_3)->create();

        $this->getJson(route('api.report.search', ['search' => $eg_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
            ]])
            ->assertJsonCount(2, 'data')
        ;
        // another sm
        $this->loginAsUser($user_2_sm);

        $this->getJson(route('api.report.search', ['search' => $eg_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_3->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_result_for_tm()
    {
        $role = Role::query()->where('role', Role::ROLE_TM)->first();
        $user_1_tm = $this->userBuilder->setRole($role)->create();
        $user_2_tm = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_1_tm);

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach([$user_1_tm->id]);

        $dealer_2 = Dealer::query()->where('country', "DE - German")->first();
        $dealer_2->users()->attach([$user_2_tm->id]);

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();

        $eg_1 = EquipmentGroup::query()->first();

        $user_2 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_2)->create();

        $rep_1 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_2)->create();
        $rep_2 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_2)->create();

        $rep_3 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_3)->create();

        $this->getJson(route('api.report.search', ['search' => $eg_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
            ]])
            ->assertJsonCount(2, 'data')
        ;
        // another tm
        $this->loginAsUser($user_2_tm);

        $this->getJson(route('api.report.search', ['search' => $eg_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_3->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_result_for_tmd()
    {
        $role = Role::query()->where('role', Role::ROLE_TMD)->first();
        $user_1_tmd = $this->userBuilder->setRole($role)->create();
        $user_2_tmd = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_1_tmd);

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach([$user_1_tmd->id]);

        $dealer_2 = Dealer::query()->where('country', "DE - German")->first();
        $dealer_2->users()->attach([$user_2_tmd->id]);

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();

        $eg_1 = EquipmentGroup::query()->first();

        $user_2 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_2)->create();

        $rep_1 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_2)->create();
        $rep_2 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_2)->create();

        $rep_3 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_3)->create();

        $this->getJson(route('api.report.search', ['search' => $eg_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
            ]])
            ->assertJsonCount(2, 'data')
        ;
        // another tm
        $this->loginAsUser($user_2_tmd);

        $this->getJson(route('api.report.search', ['search' => $eg_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_3->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_search_by_dealer_name()
    {
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        $user_1 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_1);
        // DEALER
        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('country', "DE - German")->first();

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        $user_2 = $this->userBuilder->setDealer($dealer_1)->setRole($role_ps)->create();
        $user_3 = $this->userBuilder->setDealer($dealer_2)->setRole($role_ps)->create();

        $this->assertNotEquals($dealer_1->name, $dealer_2->name);

        $rep_1 = $this->reportBuilder->setUser($user_2)->create();
        $rep_2 = $this->reportBuilder->setUser($user_2)->create();
        $rep_3 = $this->reportBuilder->setUser($user_2)->create();

        $rep_4 = $this->reportBuilder->setUser($user_3)->create();

        $this->getJson(route('api.report.search', ['search' => $dealer_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
                ["id" => $rep_3->id],
            ]])
            ->assertJsonCount(3, 'data')
        ;
        // another dealer
        $this->getJson(route('api.report.search', ['search' => $dealer_2->name]))
            ->assertJson(['data' => [
                ["id" => $rep_4->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.report.search', ['search' => mb_substr($dealer_2->name, 0, -2)]))
            ->assertJson(['data' => [
                ["id" => $rep_4->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_search_by_equipment_group()
    {
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        $user_1 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_1);

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', "!=", $eg_1->id)->first();

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        $user_2 = $this->userBuilder->setRole($role_ps)->create();
        $user_3 = $this->userBuilder->setRole($role_ps)->create();

        $rep_1 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_2)->create();
        $rep_2 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_2)->create();
        $rep_3 = $this->reportBuilder->setEquipmentGroup($eg_1)->setUser($user_3)->create();

        $rep_4 = $this->reportBuilder->setEquipmentGroup($eg_2)->setUser($user_3)->create();

        $this->getJson(route('api.report.search', ['search' => $eg_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
                ["id" => $rep_3->id],
            ]])
            ->assertJsonCount(3, 'data')
        ;
        // another eg
        $this->getJson(route('api.report.search', ['search' => $eg_2->name]))
            ->assertJson(['data' => [
                ["id" => $rep_4->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.report.search', ['search' => mb_substr($eg_2->name, 0, -2)]))
            ->assertJson(['data' => [
                ["id" => $rep_4->id],
            ], 'meta' => ["per_page" => Report::DEFAULT_PER_PAGE]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_search_by_machine_serial_number()
    {
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        $user_1 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_1);

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        $user_2 = $this->userBuilder->setRole($role_ps)->create();
        $user_3 = $this->userBuilder->setRole($role_ps)->create();

        $machineSerial_1 = '021gDS543K';
        $machineSerial_2 = '021gDS543KDR';
        $machineSerial_3 = '1BD21gDS543K';

        $rep_1 = $this->reportBuilder->setMachineData([
            'machine_serial_number' => $machineSerial_1
        ])->setUser($user_2)->create();
        $rep_2 = $this->reportBuilder->setMachineData([
            'machine_serial_number' => $machineSerial_2
        ])->setUser($user_2)->create();
        $rep_3 = $this->reportBuilder->setMachineData([
            'machine_serial_number' => $machineSerial_3
        ])->setUser($user_3)->create();

        $this->getJson(route('api.report.search', ['search' => $machineSerial_1]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
            ]])
            ->assertJsonCount(2, 'data')
        ;
        // another serial number
        $this->getJson(route('api.report.search', ['search' => $machineSerial_3]))
            ->assertJson(['data' => [
                ["id" => $rep_3->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.report.search', ['search' => mb_substr($machineSerial_2, 0, -2)]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
            ]])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_search_by_client_company_name()
    {
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        $user_1 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_1);

        $client_1 = Client::query()->first();
        $client_2 = Client::query()->where('company_name', "!=", $client_1->company_name)->first();

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        $user_2 = $this->userBuilder->setRole($role_ps)->create();

        $rep_1 = $this->reportBuilder->setClientJD($client_1)->setUser($user_2)->create();
        $rep_2 = $this->reportBuilder->setClientJD($client_1)->setUser($user_2)->create();

        $rep_3 = $this->reportBuilder->setClientJD($client_2)->setUser($user_2)->create();

        $rep_4 = $this->reportBuilder->setClientCustom(['company_name' => 'company'])->setUser($user_2)->create();

        $this->getJson(route('api.report.search', ['search' => $client_1->company_name]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
            ]])
            ->assertJsonCount(2, 'data')
        ;
        // another eg
        $this->getJson(route('api.report.search', ['search' => $client_2->company_name]))
            ->assertJson(['data' => [
                ["id" => $rep_3->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.report.search', ['search' => 'company']))
            ->assertJson(['data' => []])
            ->assertJsonCount(0, 'data')
        ;

        $this->getJson(route('api.report.search', ['search' => mb_substr($client_1->company_name, 0, -2)]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
            ]])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_search_by_demo_driver_surname()
    {
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        $user_1 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_1);

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        $user_2 = $this->userBuilder->withProfile()->setRole($role_ps)->create();
        $user_3 = $this->userBuilder->withProfile()->setRole($role_ps)->create();

        $rep_1 = $this->reportBuilder->setUser($user_2)->create();
        $rep_2 = $this->reportBuilder->setUser($user_2)->create();
        $rep_3 = $this->reportBuilder->setUser($user_2)->create();

        $rep_4 = $this->reportBuilder->setUser($user_3)->create();


        $this->getJson(route('api.report.search', ['search' => $user_2->profile->last_name]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
                ["id" => $rep_3->id],
            ]])
            ->assertJsonCount(3, 'data')
        ;
        // another user
        $this->getJson(route('api.report.search', ['search' => $user_3->profile->last_name]))
            ->assertJson(['data' => [
                ["id" => $rep_4->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.report.search', ['search' => mb_substr($user_3->profile->last_name, 0, -2)]))
            ->assertJson(['data' => [
                ["id" => $rep_4->id],
            ]])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_query_per_page()
    {
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        $user_1 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_1);
        // DEALER
        $dealer_1 = Dealer::query()->first();

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        $user_2 = $this->userBuilder->setDealer($dealer_1)->setRole($role_ps)->create();

        $this->reportBuilder->setUser($user_2)->create();
        $this->reportBuilder->setUser($user_2)->create();
        $this->reportBuilder->setUser($user_2)->create();
        $this->reportBuilder->setUser($user_2)->create();

        $this->getJson(route('api.report.search', [
            'search' => $dealer_1->name,
            'perPage' => 2,
        ]))
            ->assertJson(['meta' => [
                "current_page" => 1,
                "last_page" => 2,
                "per_page" => 2,
                "total" => 4,
            ]])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_some_field()
    {
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        $user_1 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_1);

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('country', "DE - German")->first();

        $client_1 = Client::query()->first();
        $client_1->update(['company_name' => $dealer_1->name]);

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        $user_2 = $this->userBuilder->profileData([
            'last_name' => $dealer_1->name
        ])->setRole($role_ps)->create();
        $user_3 = $this->userBuilder->withProfile()->setDealer($dealer_1)->setRole($role_ps)->create();
        $user_4 = $this->userBuilder->setDealer($dealer_2)->setRole($role_ps)->create();

        $rep_1 = $this->reportBuilder->setUser($user_2)->create();
        $rep_2 = $this->reportBuilder->setUser($user_2)->create();
        $rep_3 = $this->reportBuilder->setUser($user_3)->create();

        $rep_4 = $this->reportBuilder->setClientJD($client_1)->setUser($user_4)->create();
        $rep_5 = $this->reportBuilder->setMachineData([
            'machine_serial_number' => $dealer_1->name
        ])->setUser($user_4)->create();

        $rep_6 = $this->reportBuilder
            ->setMachineData(['machine_serial_number' => "90786HKJGF"])
            ->setUser($user_4)->create();

        $this->getJson(route('api.report.search', ['search' => $dealer_1->name]))
            ->assertJson(['data' => [
                ["id" => $rep_1->id],
                ["id" => $rep_2->id],
                ["id" => $rep_3->id],
                ["id" => $rep_4->id],
                ["id" => $rep_5->id],
            ]])
            ->assertJsonCount(5, 'data')
        ;

        $this->getJson(route('api.report.search', ['search' => mb_substr($dealer_1->name, 0, -2)]))
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_nothing_found()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $dealer Dealer */
        $dealer = Dealer::query()->first();
        /** @var $user User */
        $user = $this->userBuilder->setDealer($dealer)->setRole($role)->create();
        $this->loginAsUser($user);

        /** @var $report Report */
        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)
            ->create();

        $this->getJson(route('api.report.search', [
            'search' => 'search'
        ]))
            ->assertJsonStructure([
                "data" => [],
                "links" => [
                    "first",
                    "last",
                    "prev",
                    "next",
                ],
                "meta" => [
                    "current_page",
                    "from",
                    "last_page",
                    "path",
                    "per_page",
                    "to",
                    "total",
                ]
            ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->mock(ReportRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllForSearch")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.report.search', ['search' => 'search']))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();

        $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->getJson(route('api.report.search', [
            'search' => 'search'
        ]))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
