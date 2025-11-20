<?php

namespace Tests\Feature\Mutations\BackOffice\Auth\Employee;

use App\GraphQL\Mutations\BackOffice\Auth\Employee\EmployeeTokenRefreshMutation;
use App\GraphQL\Mutations\BackOffice\Auth\LoginMutation;
use App\Models\Employees\Employee;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class EmployeeTokenRefreshMutationTest extends TestCase
{
    public const MUTATION = EmployeeTokenRefreshMutation::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);

        $this->passportInit();
    }

    /** @test */
    public function success_refresh_token(): void
    {
        $password = 'Password123';

        $employee = $this->employeeBuilder->create();

        $query = sprintf(
            'mutation { %s(username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token } }',
            LoginMutation::NAME,
            $employee->email->getValue(),
            $password
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        [LoginMutation::NAME => $data] = $result->json('data');

        $refreshToken = $data['refresh_token'];

        $refreshQuery = sprintf(
            'mutation { %s (refresh_token: "%s") {refresh_token access_expires_in token_type access_token guard} }',
            self::MUTATION,
            $refreshToken
        );

        $result = $this->postGraphQLBackOffice(['query' => $refreshQuery])
            ->assertOk();

        [self::MUTATION => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
        self::assertEquals($data['guard'], Employee::GUARD);
    }
}

