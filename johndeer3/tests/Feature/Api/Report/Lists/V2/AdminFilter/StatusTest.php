<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminFilter;

use App\Models\User\Role;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class StatusTest extends TestCase
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

        $user = $this->userBuilder->setRole($role)->create();

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)->setUser($user)->create();
        $rep_4 = $this->reportBuilder->setStatus(ReportStatus::EDITED)->setUser($user)->create();
        $rep_5 = $this->reportBuilder->setStatus(ReportStatus::EDITED)->setUser($user)->create();
        $rep_6 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)->setUser($user)->create();
        $rep_7 = $this->reportBuilder->setStatus(ReportStatus::VERIFY)->setUser($user)->create();

        $this->getJson(route('api.v2.reports', ['status' => ReportStatus::CREATED]))
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

        $this->getJson(route('api.v2.reports', ['status' => ReportStatus::OPEN_EDIT]))
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

        $this->getJson(route('api.v2.reports', ['status' => ReportStatus::EDITED]))
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

        $this->getJson(route('api.v2.reports', ['status' => ReportStatus::IN_PROCESS]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_6->id],
                ],
                "meta" => [
                    "total" => 1,
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.v2.reports', ['status' => ReportStatus::VERIFY]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_7->id],
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

        $user = $this->userBuilder->setRole($role)->create();

        $date = Carbon::now();
        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCreatedAt($date->subMinutes(2))->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setStatus(ReportStatus::CREATED)
            ->setCreatedAt($date->subMinutes(3))->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)
            ->setCreatedAt($date->subMinutes(4))->setUser($user)->create();
        $rep_4 = $this->reportBuilder->setStatus(ReportStatus::EDITED)
            ->setCreatedAt($date->subMinutes(5))->setUser($user)->create();
        $rep_5 = $this->reportBuilder->setStatus(ReportStatus::EDITED)
            ->setCreatedAt($date->subMinutes(6))->setUser($user)->create();
        $rep_6 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setCreatedAt($date->subMinutes(7))->setUser($user)->create();
        $rep_7 = $this->reportBuilder->setStatus(ReportStatus::VERIFY)
            ->setCreatedAt($date->subMinutes(8))->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'status' => [
                ReportStatus::CREATED,
                ReportStatus::IN_PROCESS
            ]
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_1->id],
                    ["id" => $rep_2->id],
                    ["id" => $rep_6->id],
                ],
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;

        $this->getJson(route('api.v2.reports', [
            'status' => [
                ReportStatus::OPEN_EDIT,
                ReportStatus::EDITED,
            ],
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_3->id],
                    ["id" => $rep_4->id],
                    ["id" => $rep_5->id],
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

        $user = $this->userBuilder->setRole($role)->create();

        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setUser($user)->create();
        $this->reportBuilder->setStatus(ReportStatus::CREATED)->setUser($user)->create();

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
                    return $this->getJson(route('api.v2.reports', ['status' => 'null']));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['status' => null]));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['status' => '']));
                },
                2
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['status' => 'wrong']));
                },
                0
            ],
            [
                function (){
                    return $this->getJson(route('api.v2.reports', ['status' => []]));
                },
                2
            ]
        ];
    }
}

