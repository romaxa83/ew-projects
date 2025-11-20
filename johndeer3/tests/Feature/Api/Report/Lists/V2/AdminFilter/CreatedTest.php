<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminFilter;

use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class CreatedTest extends TestCase
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

        $date_1 = '2022-01-01 13:00:00';
        $date_2 = '2022-01-03 13:00:00';
        $date_3 = '2022-01-05 13:00:00';

        $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();
        $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();
        $this->reportBuilder->setCreatedAt(Carbon::make($date_2))
            ->setUser($user)->create();
        $this->reportBuilder->setCreatedAt(Carbon::make($date_3))
            ->setUser($user)->create();
        $this->reportBuilder->setCreatedAt(Carbon::make($date_3))
            ->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'created' => [
                $date_1,
                $date_2
            ]
        ]))
            ->assertJson([
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;

        $this->getJson(route('api.v2.reports', [
            'created' => $date_2
        ]))
            ->assertJson([
                "meta" => [
                    "total" => 1,
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.v2.reports', [
            'created' => [
                $date_1,
                $date_3
            ]
        ]))
            ->assertJson([
                "meta" => [
                    "total" => 5,
                ]
            ])
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_only_data()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $date_1 = '2022-01-01 13:00:00';
        $date_1_only = '2022-01-01';
        $date_2 = '2022-01-03 13:00:00';
        $date_2_only = '2022-01-03';
        $date_3 = '2022-01-05 13:00:00';
        $date_3_only = '2022-01-05';

        $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();
        $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();
        $this->reportBuilder->setCreatedAt(Carbon::make($date_2))
            ->setUser($user)->create();
        $this->reportBuilder->setCreatedAt(Carbon::make($date_3))
            ->setUser($user)->create();
        $this->reportBuilder->setCreatedAt(Carbon::make($date_3))
            ->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'created' => [
                $date_1_only,
                $date_2_only
            ]
        ]))
            ->assertJson([
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;

        $this->getJson(route('api.v2.reports', [
            'created' => [
                $date_2_only,
                $date_2_only
            ]
        ]))
            ->assertJson([
                "meta" => [
                    "total" => 1,
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.v2.reports', [
            'created' => [
                $date_1_only,
                $date_3_only
            ]
        ]))
            ->assertJson([
                "meta" => [
                    "total" => 5,
                ]
            ])
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function fail_wrong_format_date_range()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $date_check = '01.01.2022';
        $date_not_check = '02.01.2022';
        $date_1 = '2022-01-01 13:00:00';

        $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();

        $this->getJson(route('api.v2.reports', ['created' => $date_check]))
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('api.v2.reports', ['created' => $date_not_check]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_old_format_date_range()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $date_old_format = '01.01.2022_02.01.2022';
        $date_1 = '2022-01-01 13:00:00';

        $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();

        $this->getJson(route('api.v2.reports', ['created' => $date_old_format]))
            ->assertJson(["success" => false])
        ;
    }
//
//    /**
//     * @test
//     * @dataProvider requests
//     */
//    public function ignore_query_if_wrong(\Closure $sendRequest, $count)
//    {
//        $admin = $this->userBuilder->create();
//        $this->loginAsUser($admin);
//
//        /** @var $role Role */
//        $role = Role::query()->where('role', Role::ROLE_PS)->first();
//
//        $user = $this->userBuilder->setRole($role)->create();
//
//        $date_1 = '2022-01-01 13:00:00';
//        $date_2 = '2022-01-03 13:00:00';
//
//        $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
//            ->setUser($user)->create();
//        $this->reportBuilder->setCreatedAt(Carbon::make($date_2))
//            ->setUser($user)->create();
//
//        /** @var $res TestResponse */
//        $res = $sendRequest->call($this);
//        $res->assertJson([
//            "meta" => [
//                "total" => $count,
//            ]
//        ])
//            ->assertJsonCount($count, 'data')
//        ;
//    }
//
//    public function requests(): array
//    {
//        return [
//            [
//                function (){
//                    return $this->getJson(route('api.reports', ['created' => 'null']));
//                },
//                2
//            ],
//            [
//                function (){
//                    return $this->getJson(route('api.reports', ['created' => null]));
//                },
//                2
//            ],
//            [
//                function (){
//                    return $this->getJson(route('api.reports', ['created' => '']));
//                },
//                2
//            ],
////            [
////                function (){
////                    return $this->getJson(route('api.reports', ['created' => '2020-01-01 13:00:00_2020-01-01 13:00:00']));
////                },
////                0
////            ]
//        ];
//    }
}

