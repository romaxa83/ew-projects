<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User\User;
use App\Services\Auth\UserPassportService;
use App\Services\OAuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class RefreshTokenTest extends TestCase
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
        $password = 'password';
        $user = $this->userBuilder->setPassword($password)->create();

        // логинимся через запрос. чтоб получить токен
        $res = $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password,
        ]);

        $tokenRefresh = $res->json('data.refresh_token');

        $res = $this->postJson(route('api.refresh.token'),[
            'refresh_token' => $tokenRefresh
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                "data" => [
                    "token_type",
                    "expires_in",
                    "access_token",
                    "refresh_token",
                ],
                "success",
            ])
        ;

        $this->assertNotEquals($tokenRefresh, $res->json('data.refresh_token'));
    }

    /** @test */
    public function fail_without_refresh_token()
    {
        $this->postJson(route('api.refresh.token'))
            ->assertJson($this->structureErrorResponse(["The refresh token field is required."]))
        ;
    }

    /** @test */
    public function fail_not_array_as_response()
    {
        /** @var $user User */
        $password = 'password';
        $user = $this->userBuilder->setPassword($password)->create();

        // логинимся через запрос. чтоб получить токен
        $res = $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password,
        ]);

        $tokenRefresh = $res->json('data.refresh_token');

        $this->mock(UserPassportService::class, function(MockInterface $mock){
            $mock->shouldReceive("refreshToken")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('api.refresh.token'),[
            'refresh_token' => $tokenRefresh
        ])
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function fail_return_error()
    {
        /** @var $user User */
        $password = 'password';
        $user = $this->userBuilder->setPassword($password)->create();

        // логинимся через запрос. чтоб получить токен
        $res = $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password,
        ]);

        $tokenRefresh = $res->json('data.refresh_token');

        $this->mock(UserPassportService::class, function(MockInterface $mock){
            $mock->shouldReceive("refreshToken")
                ->andReturn([
                    "error" => "has error",
                    "error_description" => "has error description",
                ]);
        });

        $this->postJson(route('api.refresh.token'),[
            'refresh_token' => $tokenRefresh
        ])
            ->assertJson($this->structureErrorResponse("has error description"))
        ;
    }
}


