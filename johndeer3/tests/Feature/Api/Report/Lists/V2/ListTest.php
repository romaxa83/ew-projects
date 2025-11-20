<?php

namespace Tests\Feature\Api\Report\Lists\V2;

use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\Report\ReportRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

// здесь все тесты от роли admin
class ListTest extends TestCase
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
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();

        /** @var $report Report */
        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user)->create();

        $this->getJson(route('api.v2.reports'))
            ->assertJsonStructure($this->structureWithPaginate([
                "id",
                "owner",
                "verify",
                "comment",
                "client_email",
                "title",
                "status",
                "created",
                "machine",
                "clients" => [
                    "john_dear_client",
                    "report_client",
                ],
                "ps" => [
                    "id",
                    "login",
                    "email",
                    "phone",
                    "status",
                    "created",
                    "updated",
                    "profile",
                    "role" => [
                        "role",
                        "alias",
                    ],
                    "lang",
                    "country",
                    "dealers",
                    "egs"
                ],
                "tm"
            ]))
            ->assertJson([
                "meta" => [
                    "current_page" => 1,
                    "total" => 3,
                    "per_page" => Report::DEFAULT_PER_PAGE
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_filter_page()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();

        /** @var $report Report */
        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'page' => 3
        ]))
            ->assertJson([
                "meta" => [
                    "current_page" => 3,
                    "total" => 3,
                    "to" => 0
                ]
            ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_filter_per_page()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();

        /** @var $report Report */
        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user)->create();
        $this->reportBuilder->setUser($user)->create();

        $this->getJson(route('api.v2.reports', [
            'per_page' => 2
        ]))
            ->assertJson([
                "meta" => [
                    "current_page" => 1,
                    "total" => 3,
                    "to" => 2,
                    "per_page" => 2
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(ReportRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllReport")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.v2.reports'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        /** @var $user User */
        $this->userBuilder->create();

        /** @var $report Report */
        $this->reportBuilder->create();

        $this->getJson(route('api.v2.reports'))
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
