<?php

namespace Tests\Feature\Api\Country;

use App\Models\BaseModel;
use App\Models\Country;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\JD\CountryRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ListTest extends TestCase
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

        $count = Country::query()->count();

        $this->getJson(route('admin.country.list'))
            ->assertJsonStructure($this->structureWithPaginate([]))
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

        $count = Country::query()->count();

        $this->getJson(route('admin.country.list', ["perPage" => 3]))
            ->assertJsonStructure($this->structureWithPaginate([]))
            ->assertJson([
                "meta" => [
                    "current_page" => 1,
                    "per_page" => 3,
                    "total" => $count
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_per_page_another()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $count = Country::query()->count();

        $this->getJson(route('admin.country.list', ["per_page" => 3]))
            ->assertJsonStructure($this->structureWithPaginate([]))
            ->assertJson([
                "meta" => [
                    "current_page" => 1,
                    "per_page" => 3,
                    "total" => $count
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_page()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $count = Country::query()->count();

        $this->getJson(route('admin.country.list', ["page" => 3]))
            ->assertJsonStructure($this->structureWithPaginate([]))
            ->assertJson([
                "meta" => [
                    "current_page" => 3,
                    "per_page" => BaseModel::DEFAULT_PER_PAGE,
                    "total" => $count
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_active()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        Country::query()->first()->update(['active' => false]);

        $countActive = Country::query()->where('active', true)->count();
        $count = Country::query()->count();

        $this->assertTrue($count > $countActive);

        $this->getJson(route('admin.country.list', ["isActive" => true]))
            ->assertJson([
                "meta" => [
                    "per_page" => BaseModel::DEFAULT_PER_PAGE,
                    "total" => $countActive
                ]
            ])
        ;
    }
    /** @test */
    public function success_as_list()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $count = Country::query()->count();

        $this->getJson(route('admin.country.list', ["paginator" => false]))
            ->assertJsonCount($count, 'data')
        ;
    }


    /** @test */
    public function fail_country_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(CountryRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllWrap")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('admin.country.list', ["isActive" => true]))
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

        $this->getJson(route('admin.country.list'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('admin.country.list'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
