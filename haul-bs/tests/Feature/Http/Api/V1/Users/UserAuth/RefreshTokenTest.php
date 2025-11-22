<?php

namespace Tests\Feature\Http\Api\V1\Users\UserAuth;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);

        $this->passportInit();
    }

    /** @test */
    public function success_refresh()
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder->password($password)->create();

        $data = [
            'email' => $model->email->getValue(),
            'password' => $password,
        ];

        // login
        $res = $this->postJson(route('api.v1.users.login'), $data);
        $token = $res->json('data.refresh_token');

        // refresh success
        $this->postJson(route('api.v1.users.refresh-token'), [
            'refresh_token' => $token
        ])
            ->assertJsonStructure([
                'data' => [
                    'token_type',
                    'expires_in',
                    'access_token',
                    'refresh_token',
                ]
            ])
        ;
    }

    /** @test */
    public function fail_wrong_token_refresh()
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder->password($password)->create();

        $data = [
            'email' => $model->email->getValue(),
            'password' => $password,
        ];

        // login
        $res = $this->postJson(route('api.v1.users.login'), $data);
        $token = $res->json('data.refresh_token');

        $res = $this->postJson(route('api.v1.users.refresh-token'), [
            'refresh_token' => $token . 'ttt'
        ])
        ;

        self::assertErrorMsg($res, "The refresh token is invalid.");
    }

    /** @test */
    public function fail_without_token_refresh()
    {
        $res = $this->postJson(route('api.v1.users.refresh-token'))
        ;

        self::assertValidationMsg($res,
            __('validation.required', ['attribute' => 'refresh token']),
            'refresh_token'
        );
    }
}

