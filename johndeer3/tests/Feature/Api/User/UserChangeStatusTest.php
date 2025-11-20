<?php

namespace Tests\Feature\Api\User;

use App\Models\User\Role;
use App\Models\User\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UserChangeStatusTest extends TestCase
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
    public function success_toggle_to_false()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder
            ->setRole($role)
            ->setStatus(1)
            ->create();

        $this->assertTrue($user->isActive());

        $this->postJson(route('admin.change-status.user', ['user' => $user]), [
            'status' => '0'
        ])
            ->assertJson($this->structureResource([
                'id' => $user->id,
                'status' => false,
            ]))
        ;

        $user->refresh();

        $this->assertFalse($user->isActive());
    }

    /** @test */
    public function success_toggle_to_true()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder
            ->setRole($role)
            ->setStatus(0)
            ->create();

        $this->assertFalse($user->isActive());

        $this->postJson(route('admin.change-status.user', ['user' => $user]), [
            'status' => '1'
        ])
            ->assertJson($this->structureResource([
                'id' => $user->id,
                'status' => true,
            ]))
        ;

        $user->refresh();

        $this->assertTrue($user->isActive());
    }

    /** @test */
    public function fail_wrong_status()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder
            ->setRole($role)
            ->setStatus(0)
            ->create();

        $this->postJson(route('admin.change-status.user', ['user' => $user]), [
            'status' => 'true'
        ])
            ->assertJson($this->structureErrorResponse([__("validation.not_in", ["attribute" => "status"])]))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(UserService::class, function(MockInterface $mock){
            $mock->shouldReceive("changeStatus")
                ->andThrows(\Exception::class, "some exception message");
        });

        $user = $this->userBuilder->create();

        $this->postJson(route('admin.change-status.user', ['user' => $user]), [
            'status' => '1'
        ])
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

        $this->postJson(route('admin.change-status.user', ['user' => $user]), [
            'status' => 'true'
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder->create();

        $this->postJson(route('admin.change-status.user', ['user' => $user]), [
            'status' => 'true'
        ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


