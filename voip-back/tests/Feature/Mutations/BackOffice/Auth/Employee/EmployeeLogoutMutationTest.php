<?php

namespace Tests\Feature\Mutations\BackOffice\Auth\Employee;

use App\GraphQL\Mutations\BackOffice\Auth\Employee\EmployeeLogoutMutation;
use App\GraphQL\Mutations\BackOffice\Auth\LoginMutation;
use App\Models\Employees\Employee;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class EmployeeLogoutMutationTest extends TestCase
{
    public const MUTATION = EmployeeLogoutMutation::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);

        $this->passportInit();
    }

    /** @test */
    public function success_logout(): void
    {
        $password = 'Password123';
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();

        $query = sprintf(
            'mutation { %s(username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token } }',
            LoginMutation::NAME,
            $employee->email->getValue(),
            $password
        );

        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        [LoginMutation::NAME => $data] = $result->json('data');

        $query = sprintf(
            'mutation { %s }',
            self::MUTATION
        );

        $this->postGraphQLBackOffice(compact('query'), ['Authorization' => 'Bearer ' . $data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => true,]]);

        $this->postGraphQLBackOffice(compact('query'), ['Authorization' => 'Bearer ' . $data['access_token']])
            ->assertOk()
            ->assertJson(['data' => [self::MUTATION => false,]]);
    }
}
