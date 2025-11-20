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

class ListAsTmTest extends TestCase
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

    // tm и tmd может видеть отчеты тех ps`ов которые привязаны к тому же дилеру что и данный sm
    // tm и tmd не может фильтровать по ps_id
    // tm и tmd может фильтровать по dealer_id
    // tm и tmd не может фильтровать по tm_id
    /** @test */
    public function success()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_TM)->first();
        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user_tm_1 = $this->userBuilder->setRole($role)->create();
        $user_tm_2 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_tm_1);

        $this->assertTrue($user_tm_1->isTM());
        $this->assertTrue($user_tm_2->isTM());

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach([$user_tm_1->id, $user_tm_2->id]);

        $dealer_2 = Dealer::query()->where([
            ['id', '!=', $dealer_1->id]
        ])->first();
        $dealer_2->users()->attach([$user_tm_1->id]);

        $dealer_3 = Dealer::query()->where([
            ['id', '!=', $dealer_1->id],
            ['id', '!=', $dealer_2->id],
        ])->first();
        $dealer_3->users()->attach([$user_tm_2->id]);

        $user_1 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_2)->create();
        $user_3 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_3)->create();

        $date = Carbon::now();
        $rep_1 = $this->reportBuilder->setUser($user_1)->setCreatedAt($date->subMinutes(2))->create();
        $rep_2 = $this->reportBuilder->setUser($user_1)->setCreatedAt($date->subMinutes(3))->create();

        $rep_3 = $this->reportBuilder->setUser($user_2)->setCreatedAt($date->subMinutes(4))->create();

        $rep_4 = $this->reportBuilder->setUser($user_3)->setCreatedAt($date->subMinutes(5))->create();
        $rep_5 = $this->reportBuilder->setUser($user_3)->setCreatedAt($date->subMinutes(6))->create();

        $this->getJson(route('api.v2.reports'))
            ->assertJson([
                "data" => [
                    [
                        "id" => $rep_1->id,
                        "owner" => false,
                        "tm" => [
                            "id" => $user_tm_1->id,
                            "login" => $user_tm_1->login,
                            "email" => $user_tm_1->email,
                            "phone" => $user_tm_1->phone,
                            "status" => $user_tm_1->status,
                            "profile" => null,
                        ]
                    ],
                    ["id" => $rep_2->id, "owner" => false],
                    ["id" => $rep_3->id, "owner" => false],
                ],
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;
        // запрос от другого sm
        $this->loginAsUser($user_tm_2);

        $this->getJson(route('api.v2.reports'))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id, "owner" => false],
                    ["id" => $rep_2->id, "owner" => false],
                    ["id" => $rep_4->id, "owner" => false],
                    ["id" => $rep_5->id, "owner" => false]
                ],
                "meta" => [
                    "total" => 4,
                ]
            ])
            ->assertJsonCount(4, 'data')
        ;
    }

    /** @test */
    public function success_ignore_ps_id_query()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_TM)->first();
        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user_tm_1 = $this->userBuilder->setRole($role)->create();
        $user_tm_2 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_tm_1);

        $this->assertTrue($user_tm_1->isTM());
        $this->assertTrue($user_tm_2->isTM());

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach([$user_tm_1->id, $user_tm_2->id]);

        $dealer_2 = Dealer::query()->where([
            ['id', '!=', $dealer_1->id]
        ])->first();
        $dealer_2->users()->attach([$user_tm_1->id]);

        $dealer_3 = Dealer::query()->where([
            ['id', '!=', $dealer_1->id],
            ['id', '!=', $dealer_2->id],
        ])->first();
        $dealer_3->users()->attach([$user_tm_2->id]);

        $user_1 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_2)->create();
        $user_3 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_3)->create();

        $date = Carbon::now();

        $rep_1 = $this->reportBuilder->setUser($user_1)->setCreatedAt($date->subMinutes(2))->create();
        $rep_2 = $this->reportBuilder->setUser($user_1)->setCreatedAt($date->subMinutes(3))->create();

        $rep_3 = $this->reportBuilder->setUser($user_2)->setCreatedAt($date->subMinutes(4))->create();

        $rep_4 = $this->reportBuilder->setUser($user_3)->create();
        $rep_5 = $this->reportBuilder->setUser($user_3)->create();

        $this->getJson(route('api.v2.reports', ['ps_id' => $user_3->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id, "owner" => false],
                    ["id" => $rep_2->id, "owner" => false],
                    ["id" => $rep_3->id, "owner" => false],
                ],
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_not_ignore_dealer_id_query()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_TM)->first();
        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user_tm_1 = $this->userBuilder->setRole($role)->create();
        $user_tm_2 = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user_tm_1);

        $this->assertTrue($user_tm_1->isTM());
        $this->assertTrue($user_tm_2->isTM());

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach([$user_tm_1->id, $user_tm_2->id]);

        $dealer_2 = Dealer::query()->where([
            ['id', '!=', $dealer_1->id]
        ])->first();
        $dealer_2->users()->attach([$user_tm_1->id]);

        $dealer_3 = Dealer::query()->where([
            ['id', '!=', $dealer_1->id],
            ['id', '!=', $dealer_2->id],
        ])->first();
        $dealer_3->users()->attach([$user_tm_2->id]);

        $user_1 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_2)->create();
        $user_3 = $this->userBuilder->setRole($role_ps)->setDealer($dealer_3)->create();

        $date = Carbon::now();

        $rep_1 = $this->reportBuilder->setUser($user_1)->setCreatedAt($date->subMinutes(2))->create();
        $rep_2 = $this->reportBuilder->setUser($user_1)->setCreatedAt($date->subMinutes(3))->create();

        $rep_3 = $this->reportBuilder->setUser($user_2)->setCreatedAt($date->subMinutes(4))->create();

        $rep_4 = $this->reportBuilder->setUser($user_3)->setCreatedAt($date->subMinutes(5))->create();
        $rep_5 = $this->reportBuilder->setUser($user_3)->setCreatedAt($date->subMinutes(6))->create();

        $this->getJson(route('api.v2.reports', ['dealer_id' => $dealer_1->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id, "owner" => false],
                    ["id" => $rep_2->id, "owner" => false],
                ],
                "meta" => [
                    "total" => 2,
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('api.v2.reports', ['dealer_id' => $dealer_2->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_3->id, "owner" => false],
                ],
                "meta" => [
                    "total" => 1,
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }
}
