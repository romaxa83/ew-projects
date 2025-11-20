<?php

namespace Tests\Feature\Api\JdData;

use App\Models\JD\Dealer;
use App\Models\User\User;
use App\Repositories\JD\DealersRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class DealerListTest extends TestCase
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

        $count = Dealer::query()->where('status', true)->count();

        $this->getJson(route('api.dealers'))
            ->assertJsonStructure([
                "data" => [
                    [
                        "id",
                        "jd_id",
                        "jd_jd_id",
                        "name",
                        "status",
                        "created",
                        "updated",
                        "country" => [
                            'id',
                            'name',
                            'alias',
                        ],
                        "users"
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
                    'per_page' => Dealer::DEFAULT_PER_PAGE,
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

        $count = Dealer::query()->where('status', true)->count();
        Dealer::query()->first()->update(['status' => false]);
        $countNew = Dealer::query()->where('status', true)->count();

        $this->assertNotEquals($count, $countNew);
        $this->assertTrue($count > $countNew);

        $this->getJson(route('api.dealers'))
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

        $this->getJson(route('api.dealers', ['page' => 4]))
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

        $this->getJson(route('api.dealers', ['perPage' => 4]))
            ->assertJson([
                "meta" => [
                    'per_page' => 4
                ]
            ])
        ;
    }

    /** @test */
    public function success_country_id()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $model = Dealer::query()->first();

        $count = Dealer::query()
            ->where('nationality_id', $model->nationality_id)
            ->count();

        $this->assertTrue($count > 0);

        $this->getJson(route('api.dealers', ['country_id' => $model->nationality_id]))
            ->assertJsonCount($count, 'data')
            ->assertJson([
                "meta" => [
                    'total' => $count
                ]
            ])
        ;
    }

    /** @test */
    public function success_for_statistic()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $count = Dealer::query()->where('status', true)->count();
        $model = Dealer::query()->first();

        $this->assertTrue($count > 0);

        $this->getJson(route('api.dealers', ['forStatistic' => true]))
            ->assertJson($this->structureResource([
                'all' => 'All',
                $model->id => $model->name
            ]))
            ->assertJsonCount($count + 1, 'data')
        ;
    }

    /** @test */
    public function success_search_name_few_model()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $search = 'MEGATR';
        $count = Dealer::query()->count();
        $countSearch = Dealer::query()->where('name', 'like', $search . '%')->count();

        $this->assertTrue($countSearch > 0);
        $this->assertNotEquals($count, $countSearch);

        $this->getJson(route('api.dealers', ['name' => $search]))
            ->assertJsonCount($countSearch, 'data')
        ;
    }

    /** @test */
    public function success_search_name_one_model()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $model = Dealer::query()->first();

        $this->getJson(route('api.dealers', ['name' => $model->name]))
            ->assertJson($this->structureResource([
                [
                    'id' => $model->id,
                    'name' => $model->name,
                ]
            ]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->mock(DealersRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllActive")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.dealers'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.dealers'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
