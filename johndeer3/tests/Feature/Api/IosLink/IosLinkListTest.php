<?php

namespace Tests\Feature\Api\IosLink;

use App\Models\JD\Client;
use App\Models\User\IosLink;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\IosLinkRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class IosLinkListTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->times(10)->create();

        $this->getJson(route('admin.ios-links.index'))
            ->assertJsonStructure($this->structureWithPaginate([
                "id",
                "code",
                "status",
                "link",
                "user_id",
                "user_name",
            ]))
            ->assertJson([
                "meta" => [
                    'current_page' => 1,
                    'per_page' => Client::DEFAULT_PER_PAGE,
                    'to' => 10,
                    'total' => 10
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_page()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->times(10)->create();

        $this->getJson(route('admin.ios-links.index', [
            "page" => 3
        ]))
            ->assertJson([
                "meta" => [
                    'current_page' => 3,
                    'to' => null,
                    'total' => 10
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_per_page()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->times(10)->create();

        $this->getJson(route('admin.ios-links.index', [
            "per_page" => 3
        ]))
            ->assertJson([
                "meta" => [
                    'current_page' => 1,
                    'to' => 3,
                    'per_page' => 3,
                    'total' => 10
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_active()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->times(3)->create(['status' => true]);
        IosLink::factory()->times(5)->create(['status' => false]);

        $this->getJson(route('admin.ios-links.index', [
            "status" => 1
        ]))
            ->assertJson([
                "meta" => [
                    'current_page' => 1,
                    'to' => 3,
                    'total' => 3
                ]
            ])
        ;

        $this->getJson(route('admin.ios-links.index', [
            "status" => 0
        ]))
            ->assertJson([
                "meta" => [
                    'current_page' => 1,
                    'to' => 5,
                    'total' => 5
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_id_desc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->create();

        $first = IosLink::query()->orderBy('id', 'desc')->first();

        $this->getJson(route('admin.ios-links.index', [
            "order_by" => "id", "order_type" => "desc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_id_asc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->create();

        $first = IosLink::query()->orderBy('id', 'asc')->first();

        $this->getJson(route('admin.ios-links.index', [
            "order_by" => "id", "order_type" => "asc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_code_desc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->create();

        $first = IosLink::query()->orderBy('code', 'desc')->first();

        $this->getJson(route('admin.ios-links.index', [
            "order_by" => "code", "order_type" => "desc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_code_asc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->create();

        $first = IosLink::query()->orderBy('code', 'asc')->first();

        $this->getJson(route('admin.ios-links.index', [
            "order_by" => "code", "order_type" => "asc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_link_desc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->create();

        $first = IosLink::query()->orderBy('link', 'desc')->first();

        $this->getJson(route('admin.ios-links.index', [
            "order_by" => "link", "order_type" => "desc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_link_asc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->create();

        $first = IosLink::query()->orderBy('link', 'asc')->first();

        $this->getJson(route('admin.ios-links.index', [
            "order_by" => "link", "order_type" => "asc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_status_desc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->create(['status' => false]);
        $first = IosLink::factory()->create(['status' => true]);

        $this->getJson(route('admin.ios-links.index', [
            "order_by" => "status", "order_type" => "desc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_status_asc()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        IosLink::factory()->create(['status' => true]);
        $first = IosLink::factory()->create(['status' => false]);

        $this->getJson(route('admin.ios-links.index', [
            "order_by" => "status", "order_type" => "asc"
        ]))
            ->assertJson([
                "data" => [
                    ["id" => $first->id]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(IosLinkRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllPaginator")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('admin.ios-links.index'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.ios-links.index'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('admin.ios-links.index'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
