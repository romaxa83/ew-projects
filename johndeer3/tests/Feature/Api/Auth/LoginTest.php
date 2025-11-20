<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User\Role;
use App\Models\User\User;
use App\Services\Auth\UserPassportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class LoginTest extends TestCase
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
    public function success_ps()
    {
        $password = 'password';
        $fcmToken = 'some_token';
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setPassword($password)
            ->setRole($role)
            ->create();

        $this->assertTrue($user->isPS());
        $this->assertNull($user->fcm_token);

        $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password,
            'fcm_token' => $fcmToken
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure($this->structureTokens())
            ->assertJson(["data" => [
                "isAdmin" => false
            ]])
        ;

        $user->refresh();
        $this->assertEquals($user->fcm_token, $fcmToken);
    }

    /** @test */
    public function success_admin()
    {
        $password = 'password';
        /** @var $user User */
        $user = $this->userBuilder
            ->setPassword($password)
            ->create();

        $this->assertTrue($user->isAdmin());

        $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure($this->structureTokens())
            ->assertJson(["data" => [
                "isAdmin" => true
            ]])
        ;
    }

    /** @test */
    public function fail_wrong_password()
    {
        $password = 'password';
        /** @var $user User */
        $user = $this->userBuilder
            ->setPassword($password)
            ->create();

        $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password . '1'
        ])
            ->assertJson($this->structureErrorResponse(__('message.user_wrong_password')))
        ;
    }

    /** @test */
    public function fail_not_found_user()
    {
        $this->postJson(route('api.login'), [
            'login' => "wrong",
            'password' => "password"
        ])
            ->assertJson($this->structureErrorResponse(__('message.user_wrong_login')))
        ;
    }

    /** @test */
    public function fail_error_from_auth_service()
    {
        $password = 'password';
        /** @var $user User */
        $user = $this->userBuilder->setPassword($password)
            ->create();

        $this->mock(UserPassportService::class, function(MockInterface $mock){
            $mock->shouldReceive("auth")
                ->andReturn(["error" => "some error"]);
        });

        $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password
        ])
            ->assertJson($this->structureErrorResponse(__('message.token_false')))
        ;
    }

    /** @test */
    public function fail_not_active_user()
    {
        $password = 'password';
        /** @var $user User */
        $user = $this->userBuilder
            ->setPassword($password)
            ->setStatus(false)
            ->create();

        $this->assertFalse($user->isActive());

        $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }
}
