<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class LogoutTest extends TestCase
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

        $token = $res->json('data.access_token');

        $this->assertEquals(\Auth::user()->id, $user->id);

        $this->getJson(route('api.logout'),[
            'Authorization' => 'Bearer ' . $token
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($this->structureSuccessResponse(__('message.user_logout')))
        ;
    }

    /** @test */
    public function fail_not_token()
    {
        /** @var $user User */
        $password = 'password';
        $user = $this->userBuilder->setPassword($password)->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.logout'))
            ->assertJson($this->structureErrorResponse(__('message.errors.not revoke token')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.logout'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

