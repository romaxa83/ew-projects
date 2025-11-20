<?php

namespace Tests\Feature\Api\User;

use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\User\RoleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class RoleListTest extends TestCase
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

        $roles = Role::query()->with(['current'])
            ->where('role', '!=', Role::ROLE_ADMIN)->get();

        $this->getJson(route('admin.role.list'),[
            'Content-Language' => \App::getLocale()
        ])
            ->assertJson($this->structureSuccessResponse([
                Role::ROLE_PS => $roles->where('role' , Role::ROLE_PS)->first()->current[0]->text,
                Role::ROLE_PSS => $roles->where('role' , Role::ROLE_PSS)->first()->current[0]->text,
                Role::ROLE_SM => $roles->where('role' , Role::ROLE_SM)->first()->current[0]->text,
                Role::ROLE_TM => $roles->where('role' , Role::ROLE_TM)->first()->current[0]->text,
                Role::ROLE_TMD => $roles->where('role' , Role::ROLE_TMD)->first()->current[0]->text,
            ]))
            ->assertJsonCount($roles->count(), 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(RoleRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getRoles")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('admin.role.list'))
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

        $this->getJson(route('admin.role.list'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('admin.role.list'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
