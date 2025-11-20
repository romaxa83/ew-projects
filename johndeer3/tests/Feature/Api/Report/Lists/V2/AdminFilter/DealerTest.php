<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminFilter;

use App\Models\JD\Dealer;
use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
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

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $date = Carbon::now();
        // dealer_1
        $rep_1 = $this->reportBuilder->setCreatedAt($date->subMinutes(2))->setUser($user_1)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt($date->subMinutes(3))->setUser($user_1)->create();

        $rep_3 = $this->reportBuilder->setCreatedAt($date->subMinutes(4))->setUser($user_2)->create();
        // dealer_1
        $rep_4 = $this->reportBuilder->setCreatedAt($date->subMinutes(5))->setUser($user_3)->create();
        $rep_5 = $this->reportBuilder->setCreatedAt($date->subMinutes(6))->setUser($user_3)->create();

        // запрос на другого ps
        $this->getJson(route('api.v2.reports', ['dealer_id' => $dealer_1->id]))
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
        $this->getJson(route('api.v2.reports', ['dealer_id' => $dealer_2->id]))
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

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_3 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $date = Carbon::now();
        // dealer_1
        $rep_1 = $this->reportBuilder->setCreatedAt($date->subMinutes(2))->setUser($user_1)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt($date->subMinutes(3))->setUser($user_1)->create();

        $rep_3 = $this->reportBuilder->setCreatedAt($date->subMinutes(4))->setUser($user_2)->create();
        // dealer_2
        $rep_4 = $this->reportBuilder->setCreatedAt($date->subMinutes(5))->setUser($user_3)->create();
        $rep_5 = $this->reportBuilder->setCreatedAt($date->subMinutes(6))->setUser($user_3)->create();

        // запрос на другого ps
        $this->getJson(route('api.v2.reports', [
            'dealer_id' => [
                $dealer_1->id,
                $dealer_2->id,
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

    /**
     * @test
     * @dataProvider requests
     */
    public function ignore_query_if_wrong(\Closure $sendRequest, $count)
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $user_2 = $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $this->reportBuilder->setUser($user_1)->create();
        $this->reportBuilder->setUser($user_1)->create();
        $this->reportBuilder->setUser($user_2)->create();

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
                    return $this->getJson(route('api.v2.reports', ['dealer_id' => 'null']));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['dealer_id' => null]));
                },
                3
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['dealer_id' => '']));
                },
                3
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['dealer_id' => 9999]));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['dealer_id' => []]));
                },
                3
            ]
        ];
    }
}

