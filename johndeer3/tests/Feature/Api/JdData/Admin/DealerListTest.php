<?php

namespace Tests\Feature\Api\JdData\Admin;

use App\Models\JD\Dealer;
use App\Models\User\Role;
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

        $count = Dealer::query()->count();

        $this->getJson(route('admin.dealers.list'))
            ->assertJsonStructure($this->structureResource([
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
            ]))
            ->assertJsonCount($count, 'data')
        ;
    }

    /** @test */
    public function success_with_not_active()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        Dealer::query()->first()->update(['status' => false]);
        $count = Dealer::query()->count();

        $this->getJson(route('admin.dealers.list'))
            ->assertJsonCount($count, 'data')
        ;
    }

    /** @test */
    public function fail_country_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(DealersRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAll")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('admin.dealers.list'))
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

        $this->getJson(route('admin.dealers.list'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('admin.dealers.list'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}



