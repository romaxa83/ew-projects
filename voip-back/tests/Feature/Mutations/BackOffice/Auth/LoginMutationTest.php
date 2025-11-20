<?php

namespace Tests\Feature\Mutations\BackOffice\Auth;

use App\GraphQL\Mutations\BackOffice\Auth\LoginMutation;
use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\ValueObjects\Email;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Kamailio\LocationBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class LoginMutationTest extends TestCase
{
    public const MUTATION = LoginMutation::NAME;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected LocationBuilder $locationBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->locationBuilder = resolve(LocationBuilder::class);

        $this->passportInit();
    }
    public function test_it_login_success_as_admin(): void
    {
        $email = new Email('admin@example.com');
        $password = 'Password123';

        $admin = Admin::factory()->new(['email' => $email])->create();

        $this->assertEmpty($admin->logins);

        $query = sprintf(
            'mutation { %s (username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token guard } }',
            self::MUTATION,
            $email,
            $password
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        [self::MUTATION => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
        self::assertArrayHasKey('guard', $data);

        $this->assertEquals(Admin::GUARD, data_get($data, 'guard'));

        $admin->refresh();

        $this->assertCount(1, $admin->logins);
    }

    /** @test  */
    public function success_login_as_employee(): void
    {
        $password = 'Password123';
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();

        $this->assertEmpty($employee->logins);
        $this->assertFalse($employee->status->isError());

        $query = sprintf(
            'mutation { %s (username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token guard } }',
            self::MUTATION,
            $employee->email->getValue(),
            $password
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        [self::MUTATION => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
        self::assertArrayHasKey('guard', $data);

        $this->assertEquals(Employee::GUARD, data_get($data, 'guard'));

        $employee->refresh();

        $this->assertCount(1, $employee->logins);
        $this->assertTrue($employee->status->isError());
    }

    /** @test  */
    public function success_login_as_employee_not_sip_to_location(): void
    {
        $sip = $this->sipBuilder->create();
        $password = 'Password123';
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->assertFalse($employee->status->isError());

        $query = sprintf(
            'mutation { %s (username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token guard } }',
            self::MUTATION,
            $employee->email->getValue(),
            $password
        );

        $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        $employee->refresh();

        $this->assertTrue($employee->status->isError());
    }

    /** @test  */
    public function success_login_as_employee_has_sip_to_location(): void
    {
        $sip = $this->sipBuilder->create();
        $password = 'Password123';
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->locationBuilder->setSip($sip)->create();

        $this->assertFalse($employee->status->isError());

        $query = sprintf(
            'mutation { %s (username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token guard } }',
            self::MUTATION,
            $employee->email->getValue(),
            $password
        );

        $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        $employee->refresh();

        $this->assertFalse($employee->status->isError());
    }

    public function test_it_try_to_login_with_non_exists_credentials(): void
    {
        $query = sprintf(
            'mutation { %s(username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token } }',
            self::MUTATION,
            'notexists_email@example.com',
            'Password134324'
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        self::assertArrayHasKey('errors', $result);

        $errors = $result->json('errors');
        $error = array_shift($errors);
        self::assertEquals('validation', $error['message']);

        self::assertEquals(
            'These credentials do not match our records.',
            array_shift($error['extensions']['validation']['password'])
        );
    }

    public function test_try_to_login_for_auth_user(): void
    {
        $email = new Email('admin@example.com');
        $password = 'Password123';

        Admin::factory()->new(['email' => $email])->create();

        $query = sprintf(
            'mutation { %s(username: "%s", password: "%s") {refresh_token access_expires_in token_type access_token } }',
            self::MUTATION,
            $email,
            $password
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        [self::MUTATION => ['access_token' => $token]] = $result->json('data');

        $this->postGraphQLBackOffice(['query' => $query], ['Authorization' => 'Bearer ' . $token])
            ->assertOk()
            ->assertJson(['errors' => [['message' => AuthorizationMessageEnum::AUTHORIZED]]]);
    }
}

