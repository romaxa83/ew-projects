<?php

namespace Tests\Feature\Mutations\BackOffice\Auth\Admin;

use App\GraphQL\Mutations\BackOffice\Auth\Admin\AdminChangePasswordMutation;
use App\Models\Admins\Admin;
use App\Models\Departments\Department;
use Illuminate\Support\Facades\Hash;
use Tests\Builders\Admins\AdminBuilder;
use Tests\TestCase;

class AdminChangePasswordMutationTest extends TestCase
{
    public const MUTATION = AdminChangePasswordMutation::NAME;

    protected AdminBuilder $adminBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->adminBuilder = resolve(AdminBuilder::class);
    }

    /** @test */
    public function success_change(): void
    {
        $password = 'Newpassword1';
        /** @var $model Admin */
        $model = $this->adminBuilder->create();

        $this->loginAsAdmin($model);

        self::assertTrue(Hash::check('Password123', $model->password));

        $data = [
            'current' => 'Password123',
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;

        self::assertTrue(Hash::check($password, $model->password));
    }

    /** @test */
    public function fail_validation_error_when_old_password_is_not_correct(): void
    {
        $admin = $this->loginAsAdmin();

        self::assertTrue(Hash::check('Password123', $admin->password));

        $password = 'Newpassword1';
        $data = [
            'current' => 'Password1234',
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ]);

        $this->assertResponseHasValidationMessage($res, 'current', [
            __('auth.password')
        ]);
    }

    /** @test */
    public function fail_validation_error_when_bad_password_confirmation(): void
    {
        $admin = $this->loginAsAdmin();

        self::assertTrue(Hash::check('Password123', $admin->password));

        $password = 'Newpassword1';
        $data = [
            'current' => 'password1',
            'password' => $password,
            'password_confirmation' => $password . '4334',
        ];

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ]);

        $this->assertResponseHasValidationMessage($res, 'password', [
            __('validation.confirmed', ['attribute' => __('validation.attributes.password')])
        ]);
    }

    /** @test */
    public function not_auth(): void
    {
        $password = 'Newpassword1';

        $this->adminBuilder->create();

        $data = [
            'current' => 'Password123',
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        $this->assertUnauthorized($res);
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    current: "%s",
                    password: "%s",
                    password_confirmation: "%s",
                )
            }',
            self::MUTATION,
            data_get($data, 'current'),
            data_get($data, 'password'),
            data_get($data, 'password_confirmation'),
        );
    }
}

