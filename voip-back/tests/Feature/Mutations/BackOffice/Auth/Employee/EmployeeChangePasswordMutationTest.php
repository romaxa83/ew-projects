<?php

namespace Tests\Feature\Mutations\BackOffice\Auth\Employee;

use App\GraphQL\Mutations\BackOffice\Auth\Employee\EmployeeChangePasswordMutation;
use App\Models\Employees\Employee;
use Illuminate\Support\Facades\Hash;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class EmployeeChangePasswordMutationTest extends TestCase
{
    public const MUTATION = EmployeeChangePasswordMutation::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function success_change(): void
    {
        $password = 'Newpassword1';
        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $this->loginAsEmployee($model);

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
        /** @var $employee Employee */
        $model = $this->employeeBuilder->create();

        $this->loginAsEmployee($model);

        self::assertTrue(Hash::check('Password123', $model->password));

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
        /** @var $employee Employee */
        $model = $this->employeeBuilder->create();

        $this->loginAsEmployee($model);

        self::assertTrue(Hash::check('Password123', $model->password));

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

        /** @var $employee Employee */
        $model = $this->employeeBuilder->create();

        $data = [
            'current' => 'password',
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
