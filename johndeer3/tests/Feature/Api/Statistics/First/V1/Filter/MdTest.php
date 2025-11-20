<?php

namespace Tests\Feature\Api\Statistics\First\V1\Filter;

use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\ModelDescription;
use App\Models\User\Role;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class MdTest extends TestCase
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

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();
        $md_3 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_2->jd_id],
        ])->first();
        $md_4 = ModelDescription::query()->where([
            ['id', '!=', $md_3->id],
            ['eg_jd_id', $eg_2->jd_id],
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)->setModelDescription($md_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)->setModelDescription($md_4)->create();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_2)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_3)->setEquipmentGroup($eg_2)->setModelDescription($md_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_2)->setModelDescription($md_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_2)->setModelDescription($md_4)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
            ->assertJson($this->structureSuccessResponse([
                $md_1->id => $md_1->name . ' (3)',
                $md_2->id => $md_2->name . ' (1)',
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_few_field()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();
        $md_3 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_2->jd_id],
        ])->first();
        $md_4 = ModelDescription::query()->where([
            ['id', '!=', $md_3->id],
            ['eg_jd_id', $eg_2->jd_id],
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)->setModelDescription($md_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)->setModelDescription($md_4)->create();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_2)->setModelDescription($md_4)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine.','.$uk,
            'dealer' => $dealer_1->id.','. $dealer_2->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
            ->assertJson($this->structureSuccessResponse([
                $md_1->id => $md_1->name . ' (3)',
                $md_2->id => $md_2->name . ' (3)',
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_field_as_all()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();
        $md_3 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_2->jd_id],
        ])->first();
        $md_4 = ModelDescription::query()->where([
            ['id', '!=', $md_3->id],
            ['eg_jd_id', $eg_2->jd_id],
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)->setModelDescription($md_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)->setModelDescription($md_4)->create();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_2)->setModelDescription($md_4)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => 'all',
            'dealer' => 'all',
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
            ->assertJson($this->structureSuccessResponse([
                $md_1->id => $md_1->name . ' (3)',
                $md_2->id => $md_2->name . ' (3)',
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

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();
        $eg_2 = EquipmentGroup::query()->where('for_statistic', true)->where('id', '!=', $eg_1->id)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();
        $md_2 = ModelDescription::query()->where([
            ['id', '!=', $md_1->id],
            ['eg_jd_id', $eg_1->jd_id],
        ])->first();
        $md_3 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_2->jd_id],
        ])->first();
        $md_4 = ModelDescription::query()->where([
            ['id', '!=', $md_3->id],
            ['eg_jd_id', $eg_2->jd_id],
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setCreatedAt(Carbon::now()->subYear())->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)->setModelDescription($md_3)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_2)->setModelDescription($md_4)->create();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_1)->setModelDescription($md_2)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($uk)
            ->setUser($user_3)->setEquipmentGroup($eg_2)->setModelDescription($md_4)->create();

        $data = [
            'year' => Carbon::now()->subYear()->year,
            'country' => 'all',
            'dealer' => 'all',
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
            ->assertJson($this->structureSuccessResponse([
                $md_1->id => $md_1->name . ' (1)',
            ]))
            ->assertJsonCount(1, 'data')
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

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::VERIFY)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::EDITED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();
        $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
            ->assertJson($this->structureSuccessResponse([
                $md_1->id => $md_1->name . ' (3)',
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

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->id]
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
            ->assertJson($this->structureSuccessResponse([]))
            ->assertJsonCount(0, 'data')
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

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'country' => $ukraine,
            'dealer' => $dealer_1->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
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

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'dealer' => $dealer_1->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
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

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
            ->assertJson($this->structureErrorResponse(["The dealer field is required."]))
        ;
    }

    /** @test */
    public function fail_without_eg()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();

        $eg_1 = EquipmentGroup::query()->where('for_statistic', true)->first();

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
            ->assertJson($this->structureErrorResponse(["The eg field is required."]))
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

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
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

        $md_1 = ModelDescription::query()->where([
            ['eg_jd_id', $eg_1->jd_id]
        ])->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        list($ukraine, $uk) = ['Ukraine', 'UK'];

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setCountry($ukraine)
            ->setUser($user_1)->setEquipmentGroup($eg_1)->setModelDescription($md_1)->create();

        $data = [
            'year' => Carbon::now()->year,
            'country' => $ukraine,
            'dealer' => $dealer_1->id,
            'eg' => $eg_1->id,
        ];

        $this->getJson(route('api.statistic.filter.md', $data))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
