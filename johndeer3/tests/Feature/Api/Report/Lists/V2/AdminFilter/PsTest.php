<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminFilter;

use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class PsTest extends TestCase
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

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->create();
        $user_2 = $this->userBuilder->setRole($role)->create();

        $rep_1 = $this->reportBuilder->setUser($user_1)->create();
        $rep_2 = $this->reportBuilder->setUser($user_1)->create();
        $rep_3 = $this->reportBuilder->setUser($user_2)->create();

        $this->getJson(route('api.v2.reports', ['ps_id' => $user_1->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                    ["id" => $rep_2->id],
                ],
                "meta" => [
                    "total" => 2,
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
        // запрос на другого ps
        $this->getJson(route('api.v2.reports', ['ps_id' => $user_2->id]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_3->id],
                ],
                "meta" => [
                    "total" => 1,
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_some_value()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->create();
        $user_2 = $this->userBuilder->setRole($role)->create();

        $date = Carbon::now();
        $rep_1 = $this->reportBuilder->setCreatedAt($date->subMinutes(2))->setUser($user_1)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt($date->subMinutes(3))->setUser($user_1)->create();
        $rep_3 = $this->reportBuilder->setCreatedAt($date->subMinutes(4))->setUser($user_2)->create();

        $this->getJson(route('api.v2.reports', [
            'ps_id' => [
                $user_1->id,
                $user_2->id,
            ],
        ]))
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
    }

    /**
     * @test
     * @dataProvider requests
     */
    public function ignore_query_if_wrong(\Closure $sendRequest, $count)
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->create();
        $user_2 = $this->userBuilder->setRole($role)->create();

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
                    return $this->getJson(route('api.v2.reports', ['ps_id' => 'null']));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['ps_id' => null]));
                },
                3
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['ps_id' => '']));
                },
                3
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['ps_id' => 9999]));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['ps_id' => []]));
                },
                3
            ]
        ];
    }
}

