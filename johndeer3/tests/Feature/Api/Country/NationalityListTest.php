<?php

namespace Tests\Feature\Api\Country;

use App\Models\BaseModel;
use App\Models\User\Nationality;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\NationalityRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class NationalityListTest extends TestCase
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
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $count = Nationality::query()->count();

        $this->getJson(route('api.nationalities.list'))
            ->assertJsonStructure($this->structureWithPaginate([
                "id",
                "name",
                "alias"
            ]))
            ->assertJson([
                "meta" => [
                    "current_page" => 1,
                    "per_page" => BaseModel::DEFAULT_PER_PAGE,
                    "total" => $count
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_per_page()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->getJson(route('api.nationalities.list', ["perPage" => 3]))
            ->assertJsonStructure($this->structureWithPaginate([]))
            ->assertJson([
                "meta" => [
                    "current_page" => 1,
                    "per_page" => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_page()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->getJson(route('api.nationalities.list', ["page" => 3]))
            ->assertJsonStructure($this->structureWithPaginate([]))
            ->assertJson([
                "meta" => [
                    "current_page" => 3,
                    "per_page" => BaseModel::DEFAULT_PER_PAGE
                ]
            ])
        ;
    }

    /** @test */
    public function fail_toggle_active_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(NationalityRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllPaginator")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.nationalities.list'))
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

        $this->getJson(route('api.nationalities.list'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.nationalities.list'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
