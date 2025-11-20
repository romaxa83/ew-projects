<?php

namespace Tests\Feature\Api\Report\Lists\V2;

use App\Models\JD\Dealer;
use App\Models\User\Role;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ListAsPssTest extends TestCase
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
        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setDealer($dealer_1)
            ->setRole($role)->create();
        $this->loginAsUser($user);

        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $date = Carbon::now();

        $rep_1 = $this->reportBuilder->setCreatedAt($date->subMinutes(2))->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt($date->subMinutes(3))->setUser($user)->create();

        $rep_3 = $this->reportBuilder->setCreatedAt($date->subMinutes(4))->setUser($user_2)->create();

        $rep_4 = $this->reportBuilder->setCreatedAt($date->subMinutes(5))->setUser($user_3)->create();
        $rep_5 = $this->reportBuilder->setCreatedAt($date->subMinutes(6))->setUser($user_3)->create();

        $this->getJson(route('api.v2.reports'))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id, "owner" => false],
                    ["id" => $rep_2->id, "owner" => false],
                    ["id" => $rep_3->id, "owner" => false],
                    ["id" => $rep_4->id, "owner" => false],
                    ["id" => $rep_5->id, "owner" => false],
                ],
                "meta" => [
                    "total" => 5,
                ]
            ])
            ->assertJsonCount(5, 'data')
        ;
    }

    // pss не может фильтровать по ps_id
    /** @test */
    public function success_ignore_ps_id_query()
    {
        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setDealer($dealer_1)
            ->setRole($role)->create();
        $this->loginAsUser($user);

        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user_2)->create();
        $this->reportBuilder->setUser($user_3)->create();
        $this->reportBuilder->setUser($user_3)->create();

        $this->getJson(route('api.v2.reports', ['ps_id' => $user_3->id]))
            ->assertJson([
                "meta" => [
                    "total" => 5,
                ]
            ])
            ->assertJsonCount(5, 'data')
        ;
    }

    // pss не может фильтровать по dealer_id
    /** @test */
    public function success_ignore_dealer_id_query()
    {
        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setDealer($dealer_1)
            ->setRole($role)->create();
        $this->loginAsUser($user);

        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user_2)->create();
        $this->reportBuilder->setUser($user_3)->create();
        $this->reportBuilder->setUser($user_3)->create();

        $this->getJson(route('api.v2.reports', ['dealer_id' => $dealer_2->id]))
            ->assertJson([
                "meta" => [
                    "total" => 5,
                ]
            ])
            ->assertJsonCount(5, 'data')
        ;
    }

    // pss не может фильтровать по tm_id
    /** @test */
    public function success_ignore_tm_id_query()
    {
        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();

        $tm_1 = $this->userBuilder->setRole($role_tm)->create();
        $tm_2 = $this->userBuilder->setRole($role_tm)->create();

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach($tm_1);

        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();
        $dealer_2->users()->attach($tm_2);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setDealer($dealer_1)
            ->setRole($role)->create();
        $this->loginAsUser($user);

        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user_2)->create();
        $this->reportBuilder->setUser($user_3)->create();
        $this->reportBuilder->setUser($user_3)->create();

        $this->getJson(route('api.v2.reports', ['tm_id' => $tm_2->id]))
            ->assertJson([
                "meta" => [
                    "total" => 5,
                ]
            ])
            ->assertJsonCount(5, 'data')
        ;
    }
}

