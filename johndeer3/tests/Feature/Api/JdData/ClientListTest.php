<?php

namespace Tests\Feature\Api\JdData;

use App\Models\JD\Client;
use App\Models\User\User;
use App\Repositories\JD\ClientRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ClientListTest extends TestCase
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

        $count = Client::query()->where('status', true)->count();

        $this->getJson(route('api.clients'))
            ->assertJsonStructure([
                "data" => [
                    [
                        "id",
                        "jd_id",
                        "customer_id",
                        "company_name",
                        "customer_first_name",
                        "customer_last_name",
                        "customer_second_name",
                        "phone",
                        "created",
                        "updated",
                        "region" => [
                            'id',
                            'jd_id',
                            'name',
                            'status',
                        ],
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                "meta" => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ]
            ])
            ->assertJson([
                "meta" => [
                    'current_page' => 1,
                    'per_page' => Client::DEFAULT_PER_PAGE,
                    'total' => $count
                ]
            ])
        ;
    }

    /** @test */
    public function success_only_active()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $count = Client::query()->where('status', true)->count();
        Client::query()->first()->update(['status' => false]);
        $countNew = Client::query()->where('status', true)->count();

        $this->assertNotEquals($count, $countNew);
        $this->assertTrue($count > $countNew);

        $this->getJson(route('api.clients'))
            ->assertJson([
                "meta" => [
                    'total' => $countNew
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

        $this->getJson(route('api.clients', ['page' => 4]))
            ->assertJson([
                "meta" => [
                    'current_page' => 4
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

        $this->getJson(route('api.clients', ['perPage' => 4]))
            ->assertJson([
                "meta" => [
                    'per_page' => 4
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_per_page_another()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.clients', ['per_page' => 4]))
            ->assertJson([
                "meta" => [
                    'per_page' => 4
                ]
            ])
        ;
    }

    /** @test */
    public function success_search()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $model = Client::query()->where('status', true)->first();
        $search = $model->company_name;
        $count = Client::query()
            ->where('status', true)
            ->where('company_name', 'like', '%' . $search . '%')
            ->count();

        $this->assertTrue($count > 0);

        $this->getJson(route('api.clients', ['search' => $search]))
            ->assertJson([
                "meta" => [
                    'total' => $count
                ]
            ])
        ;
    }

    /** @test */
    public function success_as_list()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $count = Client::query()
            ->where('status', true)
            ->count();

        $this->assertTrue($count > 0);

        $this->getJson(route('api.clients', ["paginator" => false]))
            ->assertJsonCount($count, 'data')
        ;
    }

    /** @test */
    public function success_as_list_query_param_as_string()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $count = Client::query()
            ->where('status', true)
            ->count();

        $this->assertTrue($count > 0);

        $this->getJson(route('api.clients', ["paginator" => "false"]))
            ->assertJsonCount($count, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->mock(ClientRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllWrap")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.clients'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.clients'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}



