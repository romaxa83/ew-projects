<?php

namespace Tests\Feature\Mutations\Admin\Auth;

use App\Events\Admin\AdminLogged;
use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class LoginTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function login_success()
    {
        Event::fake([AdminLogged::class]);

        $builder = $this->adminBuilder();
        $admin = $builder->create();

        $data = [
            'email' => $builder->getEmail(),
            'password' => $builder->getPassword()
        ];

        $response = $this->graphQL($this->queryStr($data))->assertOk();

        $responseData = $response->json('data.adminLogin');

        $this->assertArrayHasKey('refreshToken', $responseData);
        $this->assertArrayHasKey('expiresIn', $responseData);
        $this->assertArrayHasKey('tokenType', $responseData);
        $this->assertArrayHasKey('accessToken', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('email', $responseData['user']);
        $this->assertEquals($responseData['user']['email'], $admin->email);

        Event::assertDispatched(function (AdminLogged $event) use ($admin) {
            return $event->admin->id == $admin->id;
        });
    }

    /** @test */
    public function login_fail_password()
    {
        $builder = $this->adminBuilder();
        $builder->create();

        $data = [
            'email' => $builder->getEmail(),
            'password' => 'fake_password'
        ];

        $response = $this->graphQL($this->queryStr($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.wrong_admin_login_credentials'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function login_fail_email()
    {
        $builder = $this->adminBuilder();
        $builder->create();

        $data = [
            'email' => 'fake_email@test.com',
            'password' => $builder->getPassword()
        ];

        $response = $this->graphQL($this->queryStr($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
        $this->assertEquals($response->json('errors.0.message'), __('auth.wrong_admin_login_credentials'));
    }

    /** @test */
    public function login_fail_admin_inactive()
    {
        $builder = $this->adminBuilder();
        $admin = $builder->setStatus(Admin::STATUS_INACTIVE)->create();

        $admin->refresh();

        $this->assertTrue($admin->isInActive());

        $data = [
            'email' => $builder->getEmail(),
            'password' => $builder->getPassword()
        ];

        $response = $this->graphQL($this->queryStr($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.not perm'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function login_fail_admin_soft_deleted()
    {
        $builder = $this->adminBuilder()->softDeleted();
        $admin = $builder->create();

        $admin->refresh();

        $this->assertTrue($admin->trashed());

        $data = [
            'email' => $builder->getEmail(),
            'password' => $builder->getPassword()
        ];

        $response = $this->graphQL($this->queryStr($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals($response->json('errors.0.message'), __('auth.wrong_admin_login_credentials'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public function queryStr(array $data): string
    {
        return sprintf('
            mutation {
                adminLogin(input:{email:"%s",password:"%s"}) {
                    refreshToken
                    expiresIn
                    tokenType
                    accessToken
                    user {
                        email
                    }
              }
            }',
            $data['email'],
            $data['password']
        );
    }
}
