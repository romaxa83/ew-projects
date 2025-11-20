<?php

namespace Tests\Feature\Api\Report\Lists\V2\AdminSort;

use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class SortTest extends TestCase
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
    public function success_created_desc()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $date_1 = '2022-01-01 13:00:00';
        $date_2 = '2022-01-03 13:00:00';
        $date_3 = '2022-01-05 13:00:00';

        $rep_1 = $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt(Carbon::make($date_2))
            ->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setCreatedAt(Carbon::make($date_3))
            ->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'order_by' => 'created_at'
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_3->id],
                    ["id" => $rep_2->id],
                    ["id" => $rep_1->id],
                ],
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;

    }

    /** @test */
    public function success_created_asc()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $date_1 = '2022-01-01 13:00:00';
        $date_2 = '2022-01-03 13:00:00';
        $date_3 = '2022-01-05 13:00:00';

        $rep_1 = $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt(Carbon::make($date_2))
            ->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setCreatedAt(Carbon::make($date_3))
            ->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'order_by' => 'created_at',
            'order_type' => 'asc'
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

    /** @test */
    public function success_id_asc()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $date_1 = '2022-01-01 13:00:00';
        $date_2 = '2022-01-03 13:00:00';
        $date_3 = '2022-01-05 13:00:00';

        $rep_1 = $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt(Carbon::make($date_3))
            ->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setCreatedAt(Carbon::make($date_2))
            ->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'order_by' => 'id',
            'order_type' => 'asc'
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

    /** @test */
    public function success_id_desc()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $date_1 = '2022-01-01 13:00:00';
        $date_2 = '2022-01-03 13:00:00';
        $date_3 = '2022-01-05 13:00:00';

        $rep_1 = $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt(Carbon::make($date_3))
            ->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setCreatedAt(Carbon::make($date_2))
            ->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'order_by' => 'id'
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $rep_3->id],
                    ["id" => $rep_2->id],
                    ["id" => $rep_1->id],
                ],
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_default()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $user = $this->userBuilder->setRole($role)->create();

        $date_1 = '2022-01-01 13:00:00';
        $date_2 = '2022-01-03 13:00:00';
        $date_3 = '2022-01-05 13:00:00';

        $rep_1 = $this->reportBuilder->setCreatedAt(Carbon::make($date_1))
            ->setUser($user)->create();
        $rep_2 = $this->reportBuilder->setCreatedAt(Carbon::make($date_3))
            ->setUser($user)->create();
        $rep_3 = $this->reportBuilder->setCreatedAt(Carbon::make($date_2))
            ->setUser($user)->create();

        $this->getJson(route('api.v2.reports'))
            ->assertJson([
                "data" => [
                    ["id" => $rep_2->id],
                    ["id" => $rep_3->id],
                    ["id" => $rep_1->id],
                ],
                "meta" => [
                    "total" => 3,
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;
    }
}
