<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminFilter;

use App\Models\JD\Dealer;
use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class TmTest extends TestCase
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

        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();

        $tm_1 = $this->userBuilder->setRole($role_tm)->create();
        $tm_2 = $this->userBuilder->setRole($role_tm)->create();

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach($tm_1);

        $this->assertEquals($tm_1->id, $dealer_1->tm->first()->id);

        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();
        $dealer_2->users()->attach($tm_2);

        $this->assertEquals($tm_2->id, $dealer_2->tm->first()->id);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $date = Carbon::now();
        // tm_1
        $rep_1 = $this->reportBuilder->setUser($user_1)->setCreatedAt($date->subMinutes(2))->create();
        $rep_2 = $this->reportBuilder->setUser($user_1)->setCreatedAt($date->subMinutes(3))->create();

        $rep_3 = $this->reportBuilder->setUser($user_2)->setCreatedAt($date->subMinutes(4))->create();
        // tm_2
        $rep_4 = $this->reportBuilder->setUser($user_3)->setCreatedAt($date->subMinutes(5))->create();
        $rep_5 = $this->reportBuilder->setUser($user_3)->setCreatedAt($date->subMinutes(6))->create();

        // запрос на другого ps
        $this->getJson(route('api.v2.reports', ['tm_id' => $tm_1->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                    ["id" => $rep_2->id],
                    ["id" => $rep_3->id],
                ],
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;

        // запрос на другого дилера
        $this->getJson(route('api.v2.reports', ['tm_id' => $tm_2->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_4->id],
                    ["id" => $rep_5->id],
                ],
                "meta" => [
                    "total" => 2,
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }
    /** @test */
    public function success_some_value()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();

        $tm_1 = $this->userBuilder->setRole($role_tm)->create();
        $tm_2 = $this->userBuilder->setRole($role_tm)->create();

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach($tm_1);

        $this->assertEquals($tm_1->id, $dealer_1->tm->first()->id);

        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();
        $dealer_2->users()->attach($tm_2);

        $this->assertEquals($tm_2->id, $dealer_2->tm->first()->id);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $date = Carbon::now();
        // tm_1
        $rep_1 = $this->reportBuilder->setCreatedAt($date->subMinutes(2))->setUser($user_1)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt($date->subMinutes(3))->setUser($user_1)->create();

        $rep_3 = $this->reportBuilder->setUser($user_2)->setCreatedAt($date->subMinutes(4))->create();
        // tm_2
        $rep_4 = $this->reportBuilder->setUser($user_3)->setCreatedAt($date->subMinutes(5))->create();
        $rep_5 = $this->reportBuilder->setUser($user_3)->setCreatedAt($date->subMinutes(6))->create();

        // запрос на другого ps
        $this->getJson(route('api.v2.reports', [
            'tm_id' => [
                $tm_1->id,
                $tm_2->id,
            ]
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                    ["id" => $rep_2->id],
                    ["id" => $rep_3->id],
                    ["id" => $rep_4->id],
                    ["id" => $rep_5->id],
                ],
                "meta" => [
                    "total" => 5,
                ]
            ])
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_one_tm()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();

        $tm_1 = $this->userBuilder->setRole($role_tm)->create();
        $tm_2 = $this->userBuilder->setRole($role_tm)->create();

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach([$tm_1->id, $tm_2->id]);

        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();
        $dealer_2->users()->attach($tm_2);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();
        // tm_1, tm_2
        $rep_1 = $this->reportBuilder->setUser($user_1)->create();
        $rep_2 = $this->reportBuilder->setUser($user_1)->create();

        $rep_3 = $this->reportBuilder->setUser($user_2)->create();
        // tm_2
        $rep_4 = $this->reportBuilder->setUser($user_3)->create();
        $rep_5 = $this->reportBuilder->setUser($user_3)->create();

        // запрос на другого ps
        $this->getJson(route('api.v2.reports', ['tm_id' => $tm_1->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                    ["id" => $rep_2->id],
                    ["id" => $rep_3->id],
                ],
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;

        // запрос на другого дилера
        $this->getJson(route('api.v2.reports', ['tm_id' => $tm_2->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                    ["id" => $rep_2->id],
                    ["id" => $rep_3->id],
                    ["id" => $rep_4->id],
                    ["id" => $rep_5->id],
                ],
                "meta" => [
                    "total" => 5,
                ]
            ])
            ->assertJsonCount(5, 'data')
        ;
    }

    /**
     * @test
     * @dataProvider requests
     */
    public function ignore_query_if_wrong(\Closure $sendRequest, $count)
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role_tm = Role::query()->where('role', Role::ROLE_TM)->first();

        $tm_1 = $this->userBuilder->setRole($role_tm)->create();

        $dealer_1 = Dealer::query()->first();
        $dealer_1->users()->attach([$tm_1->id]);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();

        $this->reportBuilder->setUser($user_1)->create();
        $this->reportBuilder->setUser($user_1)->create();

        /** @var $res TestResponse */
        $res = $sendRequest->call($this);
        $res->assertJson([
            "meta" => [
                "total" => $count,
            ]
        ])
            ->assertJsonCount($count, 'data')
        ;
    }

    public function requests(): array
    {
        return [
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['tm_id' => 'null']));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['tm_id' => null]));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['tm_id' => '']));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['tm_id' => 9999]));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['tm_id' => []]));
                },
                2
            ]
        ];
    }
}


