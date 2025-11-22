<?php

namespace Tests\Feature\Queries\User;

use App\Exceptions\ErrorsCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class UserCheckPassword extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $password = 'password';
        $user = $this->userBuilder()->setPassword($password)->create();
        $this->loginAsUser($user);

        $response = $this->graphQL($this->getQueryStr($password))->assertOk();

        $this->assertEquals(__('message.user.password check'),  $response->json('data.userCheckPassword.message'));
        $this->assertTrue($response->json('data.userCheckPassword.status'));
    }

    /** @test */
    public function wrong_password()
    {
        $password = 'password';
        $user = $this->userBuilder()->setPassword($password)->create();
        $this->loginAsUser($user);

        $response = $this->graphQL($this->getQueryStr('wrongggg'))->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not valid user password'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $password = 'password';
        $user = $this->userBuilder()->setPassword($password)->create();

        $response = $this->graphQL($this->getQueryStr($password));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public function getQueryStr(string $password): string
    {
        return  sprintf('{
            userCheckPassword (password: "%s"){
                code
                message
                status
                }
            }',
            $password
        );
    }
}
