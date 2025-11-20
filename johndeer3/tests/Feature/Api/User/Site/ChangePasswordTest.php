<?php

namespace Tests\Feature\Api\User\Site;

use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ChangePasswordTest extends TestCase
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
        $pass = 'new_password';
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $passOldHash = $user->password;

        $data = [
            'password' => $pass,
            'password_confirmation' => $pass
        ];

        $this->postJson(route('api.user.change-password'), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
            ]))
        ;

        $user->refresh();
        $this->assertNotEquals($user->password, $passOldHash);
        $this->assertNotEquals($user->password, $pass);
    }

    /** @test */
    public function fail_not_confirm_password()
    {
        $pass = 'new_password';
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = [
            'password' => $pass
        ];

        $this->postJson(route('api.user.change-password'), $data)
            ->assertJson($this->structureErrorResponse(["The password confirmation does not match."]))
        ;
    }

    /** @test */
    public function fail_without_password()
    {
        $pass = 'new_password';
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = [];

        $this->postJson(route('api.user.change-password'), $data)
            ->assertJson($this->structureErrorResponse(["The password field is required."]))
        ;
    }

    /** @test */
    public function fail_small_password()
    {
        $pass = 'new';
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = [
            'password' => $pass,
            'password_confirmation' => $pass
        ];

        $this->postJson(route('api.user.change-password'), $data)
            ->assertJson($this->structureErrorResponse(["The password must be at least 5 characters."]))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->mock(UserService::class, function(MockInterface $mock){
            $mock->shouldReceive("changePassword")
                ->andThrows(\Exception::class, "some exception message");
        });

        $pass = 'new_password';
        $data = [
            'password' => $pass,
            'password_confirmation' => $pass
        ];

        $this->postJson(route('api.user.change-password'), $data)
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->userBuilder->create();

        $pass = 'new_password';
        $data = [
            'password' => $pass,
            'password_confirmation' => $pass
        ];

        $this->postJson(route('api.user.change-password'), $data)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
