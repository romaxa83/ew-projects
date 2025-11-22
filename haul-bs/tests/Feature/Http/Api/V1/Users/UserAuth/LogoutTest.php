<?php

namespace Tests\Feature\Http\Api\V1\Users\UserAuth;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class LogoutTest extends TestCase
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
    public function success_logout()
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
        $token = $res->json('data.access_token');

        // logout success
        $res = $this->postJson(route('api.v1.users.logout'), [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        self::assertSimpleMsg($res, __('auth.logged_out_success'));

        // logout fail
        $res = $this->postJson(route('api.v1.users.logout'), [], [
            'Authorization' => 'Bearer ' . $token
        ]);
        self::assertErrorMsg($res, __('auth.logout_failed'));
    }
}

