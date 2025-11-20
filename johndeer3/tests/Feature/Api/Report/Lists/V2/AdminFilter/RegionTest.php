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
class RegionTest extends TestCase
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

        $uk = "UK";
        $poland = "Poland";
        $ua = "UA";

        $user = $this->userBuilder->setRole($role)->create();

        $rep_1 = $this->reportBuilder->setLocationData(['region' => $uk])->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setLocationData(['region' => $uk])->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setLocationData(['region' => $poland])->setUser($user)->create();
        $rep_4 = $this->reportBuilder->setLocationData(['region' => $ua])->setUser($user)->create();
        $rep_5 = $this->reportBuilder->setLocationData(['region' => $ua])->setUser($user)->create();

        $this->getJson(route('api.v2.reports', ['region' => $uk]))
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

        $this->getJson(route('api.v2.reports', ['region' => $poland]))
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

        $this->getJson(route('api.v2.reports', ['region' => $ua]))
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

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $uk = "UK";
        $poland = "Poland";
        $ua = "UA";

        $user = $this->userBuilder->setRole($role)->create();

        $date = Carbon::now();
        $rep_1 = $this->reportBuilder->setLocationData(['region' => $uk])
            ->setCreatedAt($date->subMinutes(2))->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setLocationData(['region' => $uk])
            ->setCreatedAt($date->subMinutes(3))->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setLocationData(['region' => $poland])
            ->setCreatedAt($date->subMinutes(4))->setUser($user)->create();
        $rep_4 = $this->reportBuilder->setLocationData(['region' => $ua])
            ->setCreatedAt($date->subMinutes(5))->setUser($user)->create();
        $rep_5 = $this->reportBuilder->setLocationData(['region' => $ua])
            ->setCreatedAt($date->subMinutes(6))->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'region' => [
                $uk,
                $ua
            ]
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                    ["id" => $rep_2->id],
                    ["id" => $rep_4->id],
                    ["id" => $rep_5->id],
                ],
                "meta" => [
                    "total" => 4,
                ]
            ])
            ->assertJsonCount(4, 'data')
        ;

        $this->getJson(route('api.v2.reports', [
            'region' => [
                $uk,
                $poland
            ]
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

        $uk = "UK";
        $poland = "Poland";

        $user = $this->userBuilder->setRole($role)->create();

        $this->reportBuilder->setLocationData(['region' => $uk])->setUser($user)->create();
        $this->reportBuilder->setLocationData(['region' => $uk])->setUser($user)->create();
        $this->reportBuilder->setLocationData(['region' => $poland])->setUser($user)->create();

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
                    return $this->getJson(route('api.v2.reports', ['region' => 'null']));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['region' => null]));
                },
                3
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['region' => '']));
                },
                3
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['region' => 'wrong']));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['region' => []]));
                },
                3
            ]
        ];
    }
}
