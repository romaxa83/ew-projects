<?php

namespace Tests\Feature\Api\Statistics\First\V1\Filter;

use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\User\Role;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class EgTest extends TestCase
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

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_2)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->setEquipmentGroup($eg_2)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureSuccessResponse([
                $models[0]->id => $models[0]->name . ' (1)',
                $models[1]->id => $models[1]->name . ' (2)',
                $models[2]->id => $models[2]->name . ' (0)',
                $models[3]->id => $models[3]->name . ' (0)',
                $models[4]->id => $models[4]->name . ' (0)',
            ]))
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_not_include_eg()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_2)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->setEquipmentGroup($eg_2)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $eg_1->id
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureSuccessResponse([
                $models[0]->id => $models[0]->name . ' (0)',
                $models[1]->id => $models[1]->name . ' (0)',
                $models[2]->id => $models[2]->name . ' (0)',
                $models[3]->id => $models[3]->name . ' (0)',
                $models[4]->id => $models[4]->name . ' (0)',
            ]))
            ->assertJsonCount(5, 'data')
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

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_2)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->setEquipmentGroup($eg_2)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealer' => $dealer_1->id
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureSuccessResponse([
                $models[0]->id => $models[0]->name . ' (2)',
                $models[1]->id => $models[1]->name . ' (2)',
                $models[2]->id => $models[2]->name . ' (0)',
                $models[3]->id => $models[3]->name . ' (0)',
                $models[4]->id => $models[4]->name . ' (0)',
            ]))
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_few_dealer()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_2)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->setEquipmentGroup($eg_2)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealer' => $dealer_1->id.','.$dealer_2->id
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureSuccessResponse([
                $models[0]->id => $models[0]->name . ' (3)',
                $models[1]->id => $models[1]->name . ' (4)',
                $models[2]->id => $models[2]->name . ' (0)',
                $models[3]->id => $models[3]->name . ' (0)',
                $models[4]->id => $models[4]->name . ' (0)',
            ]))
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_dealer_as_all()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_2)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->setEquipmentGroup($eg_2)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => 'all',
            'dealer' => 'all'
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureSuccessResponse([
                $models[0]->id => $models[0]->name . ' (3)',
                $models[1]->id => $models[1]->name . ' (4)',
                $models[2]->id => $models[2]->name . ' (0)',
                $models[3]->id => $models[3]->name . ' (0)',
                $models[4]->id => $models[4]->name . ' (0)',
            ]))
            ->assertJsonCount(5, 'data')
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

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setCreatedAt(Carbon::now()->subYear())->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_2)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_3)->setEquipmentGroup($eg_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_2)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($uk)->setUser($user_3)->setEquipmentGroup($eg_2)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'country' => 'all',
            'dealer' => 'all'
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureSuccessResponse([
                $models[0]->id => $models[0]->name . ' (1)',
                $models[1]->id => $models[1]->name . ' (0)',
                $models[2]->id => $models[2]->name . ' (0)',
                $models[3]->id => $models[3]->name . ' (0)',
                $models[4]->id => $models[4]->name . ' (0)',
            ]))
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_different_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::EDITED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::VERIFY)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();


        $data = [
            'year' => Carbon::now()->year,
            'country' => 'all',
            'dealer' => 'all'
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureSuccessResponse([
                $models[0]->id => $models[0]->name . ' (3)',
                $models[1]->id => $models[1]->name . ' (0)',
                $models[2]->id => $models[2]->name . ' (0)',
                $models[3]->id => $models[3]->name . ' (0)',
                $models[4]->id => $models[4]->name . ' (0)',
            ]))
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureSuccessResponse([
                $models[0]->id => $models[0]->name . ' (0)',
                $models[1]->id => $models[1]->name . ' (0)',
                $models[2]->id => $models[2]->name . ' (0)',
                $models[3]->id => $models[3]->name . ' (0)',
                $models[4]->id => $models[4]->name . ' (0)',
            ]))
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_wrong()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::EDITED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => 'wrong'
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureSuccessResponse([
                $models[0]->id => $models[0]->name . ' (0)',
                $models[1]->id => $models[1]->name . ' (0)',
                $models[2]->id => $models[2]->name . ' (0)',
                $models[3]->id => $models[3]->name . ' (0)',
                $models[4]->id => $models[4]->name . ' (0)',
            ]))
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function fail_without_year()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::EDITED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();

        $data = [
            'country' => $ukraine,
            'dealer' => $dealer_1->id
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureErrorResponse(["The year field is required."]))
        ;
    }

    /** @test */
    public function fail_without_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::EDITED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'dealer' => $dealer_1->id
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureErrorResponse(["The country field is required."]))
        ;
    }

    /** @test */
    public function fail_without_dealer()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::EDITED)
            ->setCountry($ukraine)->setUser($user_1)->setEquipmentGroup($eg_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureErrorResponse(["The dealer field is required."]))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $admin = $this->userBuilder
            ->setRole(Role::query()->where('role', Role::ROLE_PS)->first())
            ->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id
        ];

        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk, $poland) = ['Ukraine', 'UK', "Poland"];
        $models = EquipmentGroup::query()->where('for_statistic', true)->get();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id
        ];


        $this->getJson(route('api.statistic.filter.eg', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

